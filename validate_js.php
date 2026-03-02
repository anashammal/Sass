<?php
$file = 'c:/xampp/htdocs/system/resources/views/store_owner/pos/index.blade.php';
$content = file_get_contents($file);

preg_match_all('/<script>(.*?)<\/script>/s', $content, $matches);

foreach ($matches[1] as $index => $script) {
    echo "Checking script block #$index...\n";
    $stack = [];
    $lines = explode("\n", $script);
    $inString = false;
    $stringChar = '';
    
    foreach ($lines as $lineNum => $line) {
        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];
            
            // Very basic string skipping
            if (!$inString && ($char === '"' || $char === "'" || $char === '`')) {
                $inString = true;
                $stringChar = $char;
                continue;
            }
            if ($inString && $char === $stringChar && ($i === 0 || $line[$i-1] !== '\\')) {
                $inString = false;
                continue;
            }
            if ($inString) continue;

            if ($char === '{' || $char === '[' || $char === '(') {
                $stack[] = ['char' => $char, 'line' => $lineNum + 1];
            } elseif ($char === '}' || $char === ']' || $char === ')') {
                if (empty($stack)) {
                    echo "ERROR: Unexpected closing '$char' on line " . ($lineNum + 1) . " of script block\n";
                    echo "Line content: " . trim($line) . "\n\n";
                } else {
                    $last = array_pop($stack);
                    $expected = [')' => '(', ']' => '[', '}' => '{'][$char];
                    if ($last['char'] !== $expected) {
                        echo "ERROR: Mismatched '$char', expected closing for '{$last['char']}' from line {$last['line']} (found on line " . ($lineNum + 1) . ")\n";
                        echo "Line content: " . trim($line) . "\n\n";
                    }
                }
            }
        }
    }
    
    while (!empty($stack)) {
        $last = array_pop($stack);
        echo "ERROR: Unclosed '{$last['char']}' from line {$last['line']}\n";
    }
}
echo "Done checking $file\n";
