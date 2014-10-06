IRediscent
========
[![Build Status](https://travis-ci.org/joegreen0991/irediscent.svg)](https://travis-ci.org/joegreen0991/irediscent)  [![Coverage Status](https://coveralls.io/repos/joegreen0991/irediscent/badge.png?branch=master)](https://coveralls.io/r/joegreen0991/irediscent?branch=master)

Lightweight php handler for iredis with redisent fallback

##Installation

Install via composer

~~~
#Add the following to your `composer.json`

{
    "require": {
        "irediscent/irediscent": "1.*"
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


Pipelined MultiExec

~~~

$redis = new Irediscent();


$redis->multiExec(function($redis){

    $redis->set('foo',1);
    $redis->set('bar',2);

});

~~~

Standard multi exec

~~~
$redis->multi();

$redis->set('foo',1);
$redis->set('bar',2);

$redis->exec();

~~~


Multi Server Sentinel Connection Provider

The provider will attempt to connect to each sentinel provider in turn and retreive information about the master/slave setup.

The resolved DSN string will then be passed into the connection object

~~~

$sentinels = array(
    '127.0.0.1:26377',
    '127.0.0.1:26378',
    '127.0.0.1:26379',
);

$redis = new Irediscent(new Irediscent\DsnProvider\SentinelProvider($sentinels));

~~~
