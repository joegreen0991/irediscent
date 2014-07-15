IRedisent
========
[![Build Status](https://travis-ci.org/joegreen0991/irediscent.svg)](https://travis-ci.org/joegreen0991/irediscent)  [![Coverage Status](https://coveralls.io/repos/joegreen0991/irediscent/badge.png?branch=master)](https://coveralls.io/r/joegreen0991/irediscent?branch=master)

Lightweight php handler for iredis with redisent fallback

##Installation

Install via composer

~~~
#Add the following to your `composer.json`

{
    "require": {
        "irediscent/irediscent": "*"
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
