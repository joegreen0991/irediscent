IRedisent
========

Lightweight php handler for iredis with redisent fallback

##Installation

Install via composer

~~~
#Add the following to your `composer.json`

{
    "require": {
        "joegreen0991/iredisent": "*"
    }
}
~~~


##Usage

Basic usage

~~~

$redis = new IRedisent();

$redis->set('test',1);

echo $redis->get('test'); // 1

~~~

Pipeline

~~~

$redis = new IRedisent();


$redis->pipeline(function($redis){

    $redis->set('foo',1);
    $redis->set('bar',2);

});


//Or

$redis->pipeline();

$redis->set('foo',1);
$redis->set('bar',2);

$redis->uncork();

~~~


MultiExec

~~~

$redis = new IRedisent();


$redis->multiExec(function($redis){

    $redis->set('foo',1);
    $redis->set('bar',2);

});


//Or

$redis->multi();

$redis->set('foo',1);
$redis->set('bar',2);

$redis->exec();

~~~