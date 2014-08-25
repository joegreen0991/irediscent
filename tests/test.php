<?php
include __DIR__ . '/../vendor/autoload.php';

use Irediscent\Connection\SocketConnection;
use Irediscent\Connection\Serializer\PurePhp;

$redis = new Irediscent(new SocketConnection(null, new PurePhp()));

$pre = 'testmanual:';

$redis->del($pre . 'testzrange');

$score = 5000;

$id = 0;

$redis->pipeline();
while($score--)
{
    $redis->hmset($pre . 'testzrange:h:'. $id, 'data1', 1, 'data2', 2);
    $redis->zadd($pre . 'testzrange', $score, $id++);
}
$redis->uncork();

//$redis->zrangebyscore($pre . 'testzrange', 0, 'inf', 'WITHSCORES');

$cmd =
    "local list = redis.call('zrangebyscore', KEYS[1], 0, 'inf')" . PHP_EOL .
    "local data = {}" . PHP_EOL .
    "for i, dataId in ipairs(list) do" . PHP_EOL .
        "table.insert(data, redis.call('hgetall', KEYS[2] .. dataId)) " . PHP_EOL .
    "end" . PHP_EOL .
    "return data";

$redis->eval($cmd, 2, $pre . 'testzrange', $pre .'testzrange:h:');