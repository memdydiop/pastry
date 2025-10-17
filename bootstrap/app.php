<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        //$middleware->web(append: [
        //    // ... autres middlewares
        //    \App\Http\Middleware\ProfileCompleted::class, // <-- Ajoutez votre middleware ici
        //]);

        //$middleware->alias([
        //    'profile.completed' => \App\Http\Middleware\ProfileCompleted::class,
        //]);
        
        $middleware->alias([
            'profile.completed' => \App\Http\Middleware\EnsureProfileIsCompleted::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
