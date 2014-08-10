<?php

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/IredisBenchmark.php';
include __DIR__ . '/PurePhpBenchmark.php';

echo "Performing IRedis benchmark\n";
$iredis = new \benchmark\Irediscent\IredisBenchmark();

$iredis->run();

echo "Performing Php benchmark\n";
$php = new \benchmark\Irediscent\PurePhpBenchmark();

$php->run();