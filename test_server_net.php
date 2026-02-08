<?php
echo "Diagnostic: Checking Mail Queue and Connectivity from Server Level\n\n";

echo "1. Checking Postfix Queue (if accessible):\n";
$output = shell_exec('mailq 2>&1');
echo $output ? $output : "No output or command not available.\n";

echo "\n2. Checking outbound connectivity to Gmail SMTP (Port 25):\n";
$fp = @fsockopen("gmail-smtp-in.l.google.com", 25, $errno, $errstr, 5);
if (!$fp) {
    echo "ERROR: Could not connect to Gmail on port 25 ($errstr)\n";
} else {
    echo "SUCCESS: Connection to Gmail on port 25 is OPEN.\n";
    fclose($fp);
}

echo "\n3. Checking outbound connectivity to Gmail SMTP (Port 587):\n";
$fp = @fsockopen("smtp.gmail.com", 587, $errno, $errstr, 5);
if (!$fp) {
    echo "ERROR: Could not connect to Gmail on port 587 ($errstr)\n";
} else {
    echo "SUCCESS: Connection to Gmail on port 587 is OPEN.\n";
    fclose($fp);
}
