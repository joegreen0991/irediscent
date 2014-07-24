<?php namespace Irediscent\Connection;

use Irediscent\Connection\Util\SocketObject;
use Irediscent\Exception\ConnectionException;
use Irediscent\Exception\TransmissionException;
use Irediscent\Exception\RedisException;
use Irediscent\Exception\UnknownResponseException;

class SocketConnection extends ConnectionAbstract {

    protected $timeout;

    protected $socket;

    public function __construct($dsn = null, $timeout = null)
    {
        $this->timeout = $timeout;

        $this->socket = new SocketObject();

        parent::__construct($dsn);
    }

    public function setSocketObject(SocketObject $socketObject)
    {
        $this->socket = $socketObject;
    }

    public function connect()
    {
        $connection = $this->dsn->getMasterDsn();

        $this->redis = $this->socket->open($connection['host'], $connection['port'], $errno, $errstr, $this->timeout);

        if ($this->redis === false)
        {
            throw new ConnectionException("Could not connect to {$connection['host']}:{$connection['port']}; {$errno} - {$errstr}");
        }
    }

    public function disconnect()
    {
        $this->socket->close($this->redis);

        $this->redis = null;
    }

    public function write($data)
    {
        $this->safeConnect();

        $this->writeRaw($data);

        return $this->readResponse();
    }

    public function multiWrite($data)
    {
        $this->safeConnect();

        /* Open a Redis connection and execute the queued commands */
        foreach ($data as $rawCommand)
        {
            $this->writeRaw($rawCommand);
        }

        // Read in the results from the pipelined commands
        $responses = array();
        for ($i = 0; $i < count($data); $i++)
        {
            $responses[] = $this->readResponse();
        }

        return $responses;
    }

    private function writeRaw($data)
    {
        $crlf = "\r\n";
        $command = '*' . count($data) . $crlf;
        foreach ($data as $arg) {
            $command .= '$' . strlen($arg) . $crlf . $arg . $crlf;
        }

        for ($written = 0; $written < strlen($command); $written += $fwrite)
        {
            $fwrite = $this->socket->write($this->redis, substr($command, $written));

            if ($fwrite === false || $fwrite <= 0)
            {
                throw new TransmissionException('Failed to write entire command to stream');
            }
        }
    }

    private function readResponse()
    {
        $chunk  = $this->socket->gets($this->redis);

        if ($chunk === false || $chunk === '') {
            throw new TransmissionException('Failed to read response from stream');
        }

        $prefix  = $chunk[0];
        $payload = substr($chunk, 1, -2);

        switch ($prefix) {
            case '-':
                throw new RedisException($payload);
            case ':':
                return (int) $payload;
            case '+':
                return $payload;
            case '*':
                $count = (int) $payload;
                if ($count === -1) {
                    return NULL;
                }
                $response = array();
                for ($i = 0; $i < $count; $i++) {
                    $response[] = $this->readResponse();
                }
                return $response;
            case '$':

                if (($size = (int) $payload) === -1) {
                    return null;
                }
                $response = '';
                $read = 0;
                
                if($size)
                {
                    do {
                        $chunk = $this->socket->read($this->redis, min($size - $read, 4096));
    
                        if ($chunk === false || $chunk === '')
                        {
                            throw new TransmissionException('Failed to read response from stream');
                        }
    
                        $read += strlen($chunk);
                        $response .= $chunk;
    
                    } while ($read < $size);
                }

                $this->socket->read($this->redis, 2);

                return $response;

            default:
                throw new UnknownResponseException("Unknown response: $prefix");
        }
    }
}
