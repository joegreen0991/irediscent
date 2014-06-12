IRedisent
========

Lightweight php handler for iredis with redisent fallback

##Installation

Install via composer

~~~
#Add the following to your `composer.json`

{
    "require": {
        "joegreen0991/irediscent": "*"
    }
}
~~~


##Usage

Basic usage

~~~

$redis = new Irediscent();

$redis->set('test',1);

echo $redis->get('test'); // 1

~~~

Pipeline

~~~

$redis = new Irediscent();


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

$redis = new Irediscent();


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