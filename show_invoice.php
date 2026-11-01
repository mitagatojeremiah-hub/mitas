<?php
if ($argc < 2) {
    fwrite(STDERR, "Usage: php show_invoice.php <invoice_id>\n");
    exit(1);
}
$_GET['invoice'] = $argv[1];
include __DIR__ . '/print_invoice.php';
