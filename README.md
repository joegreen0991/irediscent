IRediscent
========
[![Build Status](https://travis-ci.org/mrjgreen/irediscent.svg)](https://travis-ci.org/mrjgreen/irediscent)  [![Coverage Status](https://coveralls.io/repos/mrjgreen/irediscent/badge.png?branch=master)](https://coveralls.io/r/mrjgreen/irediscent?branch=master)

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

##Redis Sentinel
Irediscent is capable of working with redis servers in a sentiel cluster out of the box, using `SentinelProvider` DSN provider.

The provider will attempt to connect to each sentinel instance in turn until it reaches an active sentinel, and retreive information about the master/slave setup.

The resolved DSN string will then be passed into the connection object. If the redis cluster master configuration changes during the life time of the connection, a "Cannot write against a read-only slave" error may be returned from the redis server, that was previously the master. Irediscent will handle this error, re-interrogate the sentinels and recommit the write action against the new master.

~~~

$sentinels = array(
    '127.0.0.1:26377',
    '127.0.0.1:26378',
    '127.0.0.1:26379',
);

$redis = new Irediscent\IrediscentSentinelReplication($sentinels);

~~~
