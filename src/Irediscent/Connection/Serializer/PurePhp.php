<?php namespace Irediscent\Connection\Serializer;

use Irediscent\Exception\RedisException;
use Irediscent\Exception\UnknownResponseException;

class PurePhp implements  SerializerInterface {

    const CRLF = "\r\n";

    private $incomingBuffer = '';

    private $incomingOffset = 0;

    public function serialize($data)
    {
        $command = '*' . count($data) . self::CRLF;

        foreach ($data as $arg) {
            $command .= '$' . strlen($arg) . self::CRLF . $arg . self::CRLF;
        }

        return $command;
    }

    public function read($dataChunk)
    {
        $this->incomingBuffer .= $dataChunk;

        return $this->tryParsingIncomingMessages();
    }

    private function tryParsingIncomingMessages()
    {
        try {
            $message = $this->readResponse();
        }
        catch(RedisException $e)
        {
            $message = $e;
        }

        if ($message === false) {
            // restore previous position for next parsing attempt
            $this->incomingOffset = 0;
            return false;
        }

        $this->incomingBuffer = (string)substr($this->incomingBuffer, $this->incomingOffset);

        $this->incomingOffset = 0;

        if($message instanceof RedisException)
        {
            throw $message;
        }

        return $message;
    }

    private function readLine()
    {
        $line = strstr(substr($this->incomingBuffer, $this->incomingOffset), self::CRLF, true);

        if($line !== false)
        {
            $this->incomingOffset += (strlen($line) + 2);

            return $line . self::CRLF;
        }

        return false;
    }

    private function readLength($len)
    {
        $ret = substr($this->incomingBuffer, $this->incomingOffset, $len);

        if($len === strlen($ret))
        {
            $this->incomingOffset += $len;

            return $ret;
        }

        return false;
    }

    /**
     * try to parse response from incoming buffer
     *
     * ripped from jdp/redisent, with some minor modifications to read from
     * the incoming buffer instead of issuing a blocking fread on a stream
     *
     * @throws ParserException if the incoming buffer is invalid
     * @return ModelInterface|null
     * @link https://github.com/jdp/redisent
     */
    private function readResponse() {

        if (($chunk = $this->readLine()) === false)
        {
            return false;
        }

        $prefix  = $chunk[0];
        $payload = substr($chunk, 1, -2);

        switch ($prefix) {
            case '-':
                throw new RedisException($payload);
            case ':':
                return (int) $payload;
            case '+':
                return $payload === 'OK' ? true : $payload;
            case '$':
                $count = (int) $payload;
                if ($count === -1)
                {
                    return null;
                }

                $count += 2;

                $data = $this->readLength($count);

                if ($data === false)
                {
                    return false;
                }
                return substr($data,0, -2);
            /* Multi-bulk reply */
            case '*':
                $count = (int) $payload;
                if ($count === -1) {
                    return null;
                }
                $response = array();
                for ($i = 0; $i < $count; $i++) {
                    if(($res = $this->readResponse()) === false)
                    {
                        return false;
                    }
                    $response[] = $res;
                }
                return $response;
            default:
                throw new UnknownResponseException("Received '$prefix' as response type");
        }
    }
}