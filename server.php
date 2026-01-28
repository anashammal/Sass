<?php

use Illuminate\Contracts\Http\Kernel;

/**
 * -----------------------------------------------------------------
 * | Laravel Application Entry Point - Customized for Root Execution |
 * -----------------------------------------------------------------
 * This file is used to boot the Laravel application when routing 
 * from the project root (not the public directory).
 */

define('LARAVEL_START', microtime(true));

// Load Composer Autoloading
require __DIR__.'/vendor/autoload.php';

// Instantiate the application
$app = require_once __DIR__.'/bootstrap/app.php';

// Get the HTTP kernel implementation.
$kernel = $app->make(Kernel::class);

// Handle the incoming request.
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Send the response back to the client.
$response->send();

// Execute any termination tasks.
$kernel->terminate($request, $response);