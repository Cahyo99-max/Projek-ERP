<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SingleSession::class,
        ]);

        $middleware->alias([
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'active_branch' => \App\Http\Middleware\EnsureActiveBranch::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Spatie Unauthorized Exception (Role / Permission Guard)
        $exceptions->renderable(function (UnauthorizedException $e, $request) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak! Anda tidak memiliki hak akses yang diizinkan untuk tindakan ini.'
                ], 403);
            }
            return response()->view('errors.403', ['exception' => $e], 403);
        });

        // 2. HTTP 403 Access Denied Exception
        $exceptions->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Akses Ditolak! Anda tidak memiliki izin untuk membuka halaman ini.'
                ], 403);
            }
            return response()->view('errors.403', ['exception' => $e], 403);
        });

        // 3. HTTP 404 Not Found Exception
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Halaman atau data yang Anda cari tidak ditemukan.'
                ], 404);
            }
            return response()->view('errors.404', ['exception' => $e], 404);
        });
    })->create();
