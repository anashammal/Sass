<?php
$file = 'c:/xampp/htdocs/system/resources/views/store_owner/pos/index.blade.php';
$content = file_get_contents($file);

$openBrace = substr_count($content, '{');
$closeBrace = substr_count($content, '}');
$openParen = substr_count($content, '(');
$closeParen = substr_count($content, ')');

echo "Braces: Open $openBrace, Close $closeBrace (Diff: " . ($openBrace - $closeBrace) . ")\n";
echo "Parens: Open $openParen, Close $closeParen (Diff: " . ($openParen - $closeParen) . ")\n";
