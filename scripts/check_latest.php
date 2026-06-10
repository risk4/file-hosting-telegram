<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TeleFile;
$f = TeleFile::orderBy('id','desc')->first();
echo $f ? $f->original_name : 'NOFILE';
