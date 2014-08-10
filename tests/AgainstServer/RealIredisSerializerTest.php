<?php namespace tests\Irediscent;

use Irediscent\Connection\SocketConnection;
use Irediscent\Connection\Serializer\IRedis;
use Irediscent;

include_once __DIR__ . '/RealAbstractTest.php';

class RealIredisSerializerTest extends RealAbstractTest
{

    function setUp()
    {
        if(!function_exists('phpiredis_connect'))
        {
            // Stop here and mark this test as incomplete.
            $this->markTestIncomplete(
                'PHPIRedis library is not implemented'
            );
        }
        else {
            parent::setUp();
        }
    }

    protected function getConnection($conn = null)
    {
        return new Irediscent(new SocketConnection($conn, new IRedis()));
    }
}