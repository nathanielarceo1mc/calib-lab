<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\UnitUnderTest;
$u = UnitUnderTest::find(151);
if ($u) {
    $u->update(['certificate_path' => null, 'report_payload' => null]);
    echo "reset done";
} else {
    echo "not found";
}
?>
