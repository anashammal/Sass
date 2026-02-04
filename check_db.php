<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
echo "DB_CONNECTION: " . config('database.default') . "<br>";
echo "DB_USERNAME: " . config('database.connections.mysql.username') . "<br>";
echo "DB_DATABASE: " . config('database.connections.mysql.database') . "<br>";
echo "ENV DB_USERNAME: " . env('DB_USERNAME') . "<br>";
