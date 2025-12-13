<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Handle validation errors (HTTP 422)
        if ($exception instanceof ValidationException) {
            $errors = [];
            foreach ($exception->errors() as $field => $messages) {
                $errors[$field] = implode(' ', $messages);
            }

            return response()->json(
                array_merge(
                    generate_response([], 0, true, 'Validation failed'),
                    ['errors' => $errors]
                ),
                422
            );
        }

        // Handle authentication errors (HTTP 401)
        if ($exception instanceof AuthenticationException) {
            return response()->json(
                generate_response([], 0, true, 'Unauthenticated'),
                401
            );
        }        

        // Handle authorization errors (HTTP 403)
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return response()->json(
                generate_response([], 0, true, 'Forbidden'),
                403
            );
        }
        
        // Handle HTTP exceptions (404, 405, etc.)
        if ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();
            $message = $exception->getMessage() ?: \Symfony\Component\HttpFoundation\Response::$statusTexts[$status] ?? 'Error';

            return response()->json(
                generate_response([], 0, true, $message),
                $status
            );
        }



    
        // Fallback for unexpected server errors (HTTP 500)
        $errorLog = ErrorLog::create([
            'message' => $exception->getMessage(),
        ]);
    
        return response()->json(
            generate_response([], 0, true, 'Internal Server Error #' . $errorLog->id, $errorLog->id),
            500
        );
    }
}
