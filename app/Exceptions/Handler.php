<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Only AJAX/JSON callers get the JSON envelope below; everything else uses Laravel's normal
        // pages (403/404/419/500 views, validation redirects).
        $wantsJson = $request->expectsJson() || $request->isXmlHttpRequest();

        // The HTTP status must come from the exception TYPE. It used to come from getCode(), which is
        // 0 for HttpException, AuthenticationException, AuthorizationException and
        // TokenMismatchException, so every AJAX 401/403/404/419/429 was answered with HTTP 500 and
        // the page's stale-token recovery (which waits for 419) never ran. H-10.
        $status = match (true) {
            $exception instanceof ModelNotFoundException => SymfonyResponse::HTTP_NOT_FOUND,
            $exception instanceof ValidationException => SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
            $exception instanceof AuthenticationException => SymfonyResponse::HTTP_UNAUTHORIZED,
            $exception instanceof AuthorizationException => SymfonyResponse::HTTP_FORBIDDEN,
            $exception instanceof TokenMismatchException => 419,
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            default => SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR,
        };

        $message = $exception->getMessage();

        if ($exception instanceof ModelNotFoundException) {
            if (preg_match('@\\\\(\w+)\]@', $message, $matches)) {
                $model = preg_replace('/Table/i', '', $matches[1]);
                $message = "{$model} not found.";
            } else {
                $message = 'Record not found.';
            }
        } elseif ($exception instanceof ValidationException) {
            $message = $exception->validator->errors()->first();

            if (! $wantsJson) {
                return Redirect::back()->withInput()->withErrors($message);
            }
        } elseif ($exception instanceof TokenMismatchException) {
            $message = 'Your session expired. Please refresh the page and try again.';
        } elseif ($exception instanceof AuthorizationException && $message === '') {
            $message = 'This action is unauthorized.';
        } elseif ($exception instanceof HttpExceptionInterface && $message === '') {
            $message = SymfonyResponse::$statusTexts[$status] ?? 'Error';
        } elseif ($status >= 500 && ! config('app.debug') && ! $exception instanceof HttpExceptionInterface) {
            // An unexpected failure (SQL error, file error, ...): do not echo its message (table and
            // column names, paths) to the browser; it is in the log.
            $message = 'Something went wrong. Please try again or contact the administrator.';
        }

        if ($wantsJson) {
            return Response::json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return parent::render($request, $exception);
    }
}
