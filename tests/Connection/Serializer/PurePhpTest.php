<?php namespace tests\Irediscent;

use Irediscent;

class PurePhpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parseProvider
     *
     * @param type $buffer
     * @param type $expectedData
     * @param type $expectedError
     */
    public function testBatchParse($buffer, $expectedData, $expectedError)
    {
        if($expectedError)
        {
            $this->setExpectedException('Irediscent\Exception\\'. $expectedError[0], $expectedError[1]);
        }

        $parser = new Irediscent\Connection\Serializer\PurePhp();

        is_array($buffer) or $buffer = array($buffer);

        $lastBuffer = array_pop($buffer);

        foreach($buffer as $b)
        {
            $response = $parser->read($b);

            $this->assertFalse($response);
        }

        $data = $parser->read($lastBuffer);

        $this->assertEquals($expectedData, $data);
    }

    public function parseProvider()
    {
        return array_merge(
            $this->errorHandlingSet(),
            $this->nullHandlingSet(),
            $this->genericSet()
        );
    }

    public function errorHandlingSet()
    {
        return array(
            array("nSET\r\n", false, ['UnknownResponseException',  "Received 'n' as response type"]),
            array("*2\r\n$5\r\nhello\r\n@foo\r\n", false, ['UnknownResponseException',  "Received '@' as response type"]),
            array("-Error\r\n", false, ['RedisException',  "Error"]),
        );
    }

    public function nullHandlingSet()
    {
        return array(
            array("$-1\r\n", null, false),
            array("*-1\r\n", null, false),
            array("*1\r\n*-1\r\n", array(null), false),
            array("*1\r\n$-1\r\n", array(null), false),
            array(["*1\r\n$-","1\r\n"], array(null), false),
        );
    }

    public function genericSet()
    {
        return array(
            array("+OK\r\n", true, false),
            array(":123\r\n", 123, false),
            array("$3\r\nSET\r\n", "SET", false),
            array(["$3\r\nSET\r", "\n"], "SET", false),
            array("*3\r\n$3\r\nSET\r\n$1\r\na\r\n$2\r\nAS\r\n", array("SET", "a", "AS"), false),
            array("*3\r\n:1\r\n+OK\r\n*2\r\n$3\r\nASD\r\n$-1\r\n", array(1, true, array("ASD", null)), false),
            array("*4\r\n:1\r\n+OK\r\n*2\r\n$3\r\nASD\r\n$-1\r\n$2\r\nTT\r\n", array(1, true, array("ASD", null), "TT"), false),
            array(["*4\r\n:1\r\n+OK\r\n*2\r\n$3","\r\nASD\r\n$-1\r\n$2\r\nTT\r\n"], array(1, true, array("ASD", null), "TT"), false),
            array(["*4\r\n:1\r\n+OK\r\n*2\r","\n$3","\r\nASD\r\n$-1\r\n$2\r\nTT\r\n"], array(1, true, array("ASD", null), "TT"), false),
        );
    }

    /***
     * @param type $buffer
     * @param type $expectedData
     * @param type $expectedError
     */
    public function testReadAfterErrorWorks()
    {
        $parser = new Irediscent\Connection\Serializer\PurePhp();

        try{
            $parser->read("*2\r\n");
            $parser->read("$5\r\n");
            $parser->read("hello\r\n");
            $parser->read("@foo\r\n");
        }
        catch(Irediscent\Exception\Exception $e)
        {

        }

        $response = $parser->read(":123\r\n");

        $this->assertEquals(123,$response);
    }

    /***
     * @param type $buffer
     * @param type $expectedData
     * @param type $expectedError
     */
    public function testReadAfterNoScriptWorks()
    {
        $parser = new Irediscent\Connection\Serializer\PurePhp();

        try{
            $parser->read("-NOSCRIPT use eval\r\n");
        }
        catch(Irediscent\Exception\Exception $e)
        {

        }

        $response = $parser->read(":123\r\n");

        $this->assertEquals(123,$response);
    }
}
