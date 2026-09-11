<?php

use App\Exceptions\IntegrationException;
use App\Exceptions\InvalidCredentialsException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (InvalidCredentialsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
            }
        });

        $exceptions->render(function (IntegrationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ['message' => 'Unable to generate product. Please try again.'],
                    Response::HTTP_BAD_GATEWAY,
                );
            }
        });
    })->create();
