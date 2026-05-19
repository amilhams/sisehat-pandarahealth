<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$o1 = \App\Models\Owner::where('email', 'owner_1@example.com')->first();
if ($o1) {
    $o1->password = \Hash::make('password123');
    $o1->save();
    echo "SUCCESS: owner_1@example.com password updated to password123\n";
} else {
    echo "ERROR: owner_1@example.com not found\n";
}
