<?php

namespace GuoJiangClub\EC\Catering\Server\Exception;

use League\OAuth2\Server\Exception\OAuthServerException;

class UserExistsException extends OAuthServerException
{
    /**
     * {@inheritdoc}
     */
    public $httpStatusCode = 400;

    /**
     * {@inheritdoc}
     */
    public $errorType = 'invalid_request';

    /**
     * {@inheritdoc}
     */

    public function __construct()
    {
        parent::__construct('The user exists in system', 100, 'invalid_client', 401);
    }
}