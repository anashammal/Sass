<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DB SCAN START ---\n";

$tables = ['products', 'stores', 'users', 'settings', 'posts'];
$found = false;

foreach ($tables as $table) {
    try {
        // Check if table exists using raw SQL
        $tableExists = DB::select("SHOW TABLES LIKE ?", [$table]);
        if (empty($tableExists)) continue;

        echo "Checking table: $table\n";
        
        // Get columns using raw SQL
        $columns = DB::select("SHOW COLUMNS FROM `$table`");
        
        foreach ($columns as $columnObj) {
            $column = $columnObj->Field;
            $type = strtolower($columnObj->Type);
            
            // limit to text/varchar
            if (strpos($type, 'char') === false && strpos($type, 'text') === false) {
                continue;
            }

            try {
                $count = DB::table($table)->where($column, 'LIKE', '%localhost%')->count();
                if ($count > 0) {
                    echo "FOUND 'localhost' in $table.$column ($count records)\n";
                    $sample = DB::table($table)->where($column, 'LIKE', '%localhost%')->first();
                    $val = $sample->$column;
                    if (strlen($val) > 100) $val = substr($val, 0, 100) . "...";
                    echo "Sample: $val\n";
                    $found = true;
                }
            } catch (\Exception $e) {
               // ignore
            }
        }
    } catch (\Exception $e) {
        echo "Error checking $table: " . $e->getMessage() . "\n";
    }
}

if (!$found) {
    echo "No 'localhost' strings found in inspected tables.\n";
}

echo "--- DB SCAN END ---\n";
