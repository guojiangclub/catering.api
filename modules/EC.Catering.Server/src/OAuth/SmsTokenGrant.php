<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-19
 * Time: 14:16
 */

namespace ElementVip\Server\OAuth;


use ElementVip\Server\Exception\UserExistsException;
use Laravel\Passport\Bridge\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use DateInterval;
use League\OAuth2\Server\Exception\OAuthServerException;
use RuntimeException;
use Validator;

class SmsTokenGrant extends AbstractGrant
{
    /**
     * @param UserRepositoryInterface $userRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     */
    public function __construct(
        RefreshTokenRepositoryInterface $refreshTokenRepository
    )
    {
        $this->setRefreshTokenRepository($refreshTokenRepository);

        $this->refreshTokenTTL = new \DateInterval('P1M');
    }

    /**
     * Return the grant identifier that can be used in matching up requests.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'sms_token';
    }

    /**
     * Respond to an incoming request.
     *
     * @param ServerRequestInterface $request
     * @param ResponseTypeInterface $responseType
     * @param \DateInterval $accessTokenTTL
     *
     * @return ResponseTypeInterface
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    )
    {


        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request));
        $user = $this->validateUser($request, $client);

        // Finalize the requested scopes
        $scopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());

        // Issue and persist new tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        // Inject tokens into response
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClientEntityInterface $client
     * @return UserEntityInterface
     * @throws OAuthServerException
     */
    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client)
    {


        $mobile = $this->getRequestParameter('mobile', $request);
        if (is_null($mobile)) {
            throw OAuthServerException::invalidRequest('mobile');
        }

        $code = $this->getRequestParameter('code', $request);
        if (is_null($code)) {
            throw OAuthServerException::invalidRequest('code');
        }

        $type = $this->getRequestParameter('type', $request);

        if (is_null($model = config('auth.providers.users.model'))) {
            throw new RuntimeException('Unable to determine user model from configuration.');
        }

        if (!empty($type) AND $type == 'register') {  //check if mobile exists
            if ($model::where('mobile', $mobile)->first()) {
                throw new UserExistsException();
            }
        }


        $credentials = [
            'mobile' => $mobile,
            'verifyCode' => $code,
        ];

        //验证数据
        $validator = Validator::make($credentials, [
            'mobile' => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
            'verifyCode' => 'required|verify_code',
        ]);

        if ($validator->fails()) {
            throw OAuthServerException::invalidCredentials();
        }

        if ($user = $model::where('mobile', $mobile)->first()) {
            $user = new User($user);
        } else {
            $user = $model::create([
                'mobile' => $mobile
                , 'card_limit' => date('Y-m-d', time())
                , 'group_id' => 1
            ]);
            $user = new User($user);
        }

        if ($user instanceof UserEntityInterface === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidCredentials();
        }

        return $user;

    }
}