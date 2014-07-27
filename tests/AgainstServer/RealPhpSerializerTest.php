<?php namespace tests\Irediscent;

use Irediscent\Connection\SocketConnection;
use Irediscent\Connection\Serializer\PurePhp;
use Irediscent;

include_once 'RealAbstractTest.php';

class RealPhpSerializerTest extends RealAbstractTest
{
    protected function getConnection($conn = null)
    {
        return new Irediscent(new SocketConnection(new PurePhp(), $conn, null));
    }
}
