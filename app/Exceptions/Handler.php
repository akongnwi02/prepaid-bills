<?php

namespace App\Exceptions;

use Exception;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $rendered = parent::render($request, $exception);

        $error['code']    = $rendered->getStatusCode();
        $error['message'] = 'Server Error';

        if ($exception instanceof MethodNotAllowedHttpException) {
            $error['message'] = 'Method Not Allowed';
            $error['code']    = 405;
        }

        if ($exception instanceof NotFoundHttpException) {
            $error['message'] = 'Route Not found';
            $error['code']    = 404;
        }

        if ($exception instanceof ValidationException) {
            $error['message'] = 'Invalid data';
            $error['errors']  = $exception->errors();
            $error['code']    = 422;
        }

        if ($exception instanceof ResourceNotFoundException) {
            $error['message'] = $exception->getMessage();
            $error['errors']  = $exception->errors();
            $error['code']    = 404;
        }

        if ($exception instanceof TimeOutException) {
            $error['message'] = 'Timeout';
            $error['code']    = 504;
        }

        if ($exception instanceof WebDriverCurlException) {
            $error['message'] = 'Timeout';
            $error['code']    = 504;
        }

        if ($exception instanceof TokenGenerationException) {
            $error['message'] = $exception->getMessage();
            $error['code']    = 500;
        }

        if ($exception instanceof UnAuthorizationException) {
            $error['message'] = $exception->getMessage();
            $error['code']    = 401;

        }

        if ($exception instanceof BusinessErrorException) {
            $error['message'] = $exception->getMessage();
            $error['code']    = 502;
        }

        \Log::error('ExceptionHandler', array_merge($error, [
            'exception' => (string)$exception,
            'trace'     => $exception->getTrace(),
            'previous'  => $exception->getPrevious()
        ]));


        if (config('app.debug')) {
            $error['debug'] = config('app.debug') ? (string)$exception : null;
        }
        return response()->json($error, $error['code']);
    }
}
