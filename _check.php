<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\UnitUnderTest;
$u = UnitUnderTest::find(151);
var_export($u ? $u->certificate_path : null);
?>
