<?php

use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('gmao:generate-preventive-tasks')
            ->dailyAt('00:10')
            ->withoutOverlapping(30);
        $schedule->command('gmao:send-preventive-reminders')
            ->dailyAt('06:30')
            ->withoutOverlapping(30);
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'no.cache' => \App\Http\Middleware\NoCacheHeaders::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please login again.'], 419);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'انتهت الجلسة، المرجو تسجيل الدخول من جديد.',
            ]);
        });

        $exceptions->report(function (\Throwable $e): void {
            Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => request()?->fullUrl(),
                'route' => request()?->route()?->getName(),
                'user_id' => auth()->id(),
            ]);
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!app()->isProduction()) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'An unexpected error occurred.'], 500);
            }

            return response()->view('errors.generic', [], 500);
        });
    })->create();

