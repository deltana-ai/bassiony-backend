<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
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
            $errorLog = collect([
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
            ])->concat((array)collect($e->getTrace())->take(5))->toArray();
            Log::channel('slack')->error($e->getMessage(), $errorLog);
        });
    }

    public function render($request, Exception|Throwable $e): Response|JsonResponse|RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'error' => 'Unauthorized or Session has been expired. Please Login again'
            ], 401);
        }

        return parent::render($request, $e);
    }

}
