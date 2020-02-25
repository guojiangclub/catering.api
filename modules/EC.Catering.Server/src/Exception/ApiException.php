<?php

namespace GuoJiangClub\EC\Catering\Server\Exception;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class ApiException extends ExceptionHandler
{
    protected $dontReport = [

    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     *
     * @return void
     */
    public function report(Exception $exception)
    {
        if (settings('sentry_enabled') AND $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->server->get('API_PREFIX') AND $exception->getMessage() == 'Unauthenticated.') {
            $code = $exception->getCode();

            return response()->json(['message' => $exception->getMessage(), 'code' => 4, 'status' => false])
                ->header('Access-Control-Allow-Origin', '*');
        }

        return parent::render($request, $exception);
    }
}
