<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing debt statuses:\n";

$debts = DB::table('debts')->get();

foreach($debts as $debt) {
    $status = 'UNPAID';
    if ($debt->paid_amount >= $debt->amount) {
        $status = 'PAID';
    } elseif ($debt->paid_amount > 0) {
        $status = 'PARTIALLY_PAID';
    }
    
    if ($status !== $debt->status) {
        echo "Fixing debt ID {$debt->id}: {$debt->status} -> {$status}\n";
        DB::table('debts')->where('id', $debt->id)->update(['status' => $status]);
    }
}

echo "Done!\n"; 