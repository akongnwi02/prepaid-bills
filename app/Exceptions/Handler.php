<?php

namespace App\Exceptions;

use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Exception;

class Handler extends ExceptionHandler
{
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

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $error['message'] = 'Invalid data';
            $error['errors']  = $exception->errors();
            $error['code']    = 422;
        }

        if ($exception instanceof NotFoundException) {
            $error['message'] = $exception->getMessage();
            $error['errors']  = $exception->errors();
            $error['code']    = 404;
        }
        
        if ($exception instanceof UnAuthorizationException) {
            $error['message'] = $exception->getMessage();
            $error['code']    = 401;

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
