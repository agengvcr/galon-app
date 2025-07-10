<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking debt data:\n";
$debts = DB::table('debts')->select('id', 'amount', 'paid_amount', 'status')->limit(5)->get();

foreach($debts as $debt) {
    echo "ID: {$debt->id}, Amount: {$debt->amount}, Paid: {$debt->paid_amount}, Status: {$debt->status}\n";
}

echo "\nTotal debts: " . DB::table('debts')->count() . "\n"; 