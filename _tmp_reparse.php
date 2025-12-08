<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$uut = App\Models\UnitUnderTest::find(151);
if(!$uut){echo "no uut"; exit;}
$path = $uut->certificate_path['path'] ?? null;
if(!$path){echo "no path"; exit;}
$abs = Illuminate\Support\Facades\Storage::path($path);
$ctrl = new App\Http\Controllers\UnitUnderTestController();
$ref = new ReflectionClass($ctrl);
$method = $ref->getMethod('parseCertificate');
$method->setAccessible(true);
$payload = $method->invoke($ctrl, $abs);
$uut->update(['report_payload' => $payload]);
print "parsed raw_html len: ".strlen($payload['raw_html'] ?? '')."\n";
?>
