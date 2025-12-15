<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckManager;
use App\Http\Middleware\CheckActiveToken;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',       // web rotaları
        api: __DIR__.'/../routes/api.php',       // api rotaları
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        // alias olarak tanımla
        $middleware->alias([
            'check.admin' => \App\Http\Middleware\CheckAdmin::class,
            'check.manager' => \App\Http\Middleware\CheckManager::class,
            'active.token' => \App\Http\Middleware\CheckActiveToken::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->create();
