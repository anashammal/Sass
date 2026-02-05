<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$m = \DB::table('media')->where('id', 128)->first();
if ($m) {
    echo "Media 128 Found!\n";
    echo "ID: " . $m->id . "\n";
    echo "File Name: " . $m->file_name . "\n";
    echo "Model ID: " . $m->model_id . "\n";
    echo "Collection: " . $m->collection_name . "\n";
} else {
    echo "Media 128 NOT found in DB.\n";
    $latest = \DB::table('media')->orderBy('id', 'desc')->first();
    if ($latest) {
        echo "Latest Media ID in DB: " . $latest->id . "\n";
        echo "Latest File Name: " . $latest->file_name . "\n";
    }
}
