<?php namespace tests\Irediscent;

use Irediscent\Connection\IRedis;
use Irediscent;

class RealIredisTest extends RealAbstractTest
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
        return new Irediscent(new IRedis($conn));
    }
}
