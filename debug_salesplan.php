<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SalesPlan;
use Illuminate\Support\Facades\DB;

echo "Total SalesPlan: " . SalesPlan::count() . "\n";
echo "Distinct Status: " . json_encode(SalesPlan::select('status')->distinct()->pluck('status')) . "\n";
echo "Null Tanggal: " . SalesPlan::whereNull('tanggal')->count() . "\n";
echo "Not Null Tanggal: " . SalesPlan::whereNotNull('tanggal')->count() . "\n";
echo "Status 'sudah_transfer': " . SalesPlan::where('status', 'sudah_transfer')->count() . "\n";
echo "Status 'Sudah Transfer': " . SalesPlan::where('status', 'Sudah Transfer')->count() . "\n";
