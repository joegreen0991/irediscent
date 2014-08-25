<?php namespace Irediscent\Connection;

use Irediscent\Connection\Util\SocketObject;
use Irediscent\Exception\ConnectionException;
use Irediscent\Exception\RedisException;
use Irediscent\Exception\TransmissionException;
use Irediscent\Exception\UnknownResponseException;

class SocketConnection extends ConnectionAbstract {

    const DEFAULT_TIMEOUT = 5.0;

    const CRLF = "\r\n";

    protected $timeout;

    protected $socket;

    public function __construct($dsn = null, $timeout = null)
    {
        $this->timeout = is_null($timeout) ? self::DEFAULT_TIMEOUT : (float)$timeout;

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

    protected function serialize($data)
    {
        $command = '*' . count($data) . self::CRLF;

        foreach ($data as $arg) {
            $command .= '$' . strlen($arg) . self::CRLF . $arg . self::CRLF;
        }

        return $command;
    }

    protected function writeCommand($data)
    {
        $command = $this->serialize($data);

        for ($written = 0; $written < strlen($command); $written += $fwrite)
        {
            $fwrite = $this->socket->write($this->redis, substr($command, $written));

            if ($fwrite === false || $fwrite <= 0)
            {
                throw new TransmissionException('Failed to write entire command to stream');
            }
        }
    }

    protected function readResponse()
    {
        $chunk = $this->socket->gets($this->redis);

        if ($chunk === false || $chunk === '' || $chunk === null)
        {
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
                return $payload === 'OK' ? true : $payload;
            case '$':
                $count = (int) $payload;
                if ($count === -1)
                {
                    return null;
                }

                $count += 2;

                $data = $this->socket->read($this->redis, $count);

                if ($data === false)
                {
                    throw new TransmissionException("Failed to read inline data packet from server");
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
                    $response[] = $this->readResponse();
                }
                return $response;
            default:
                throw new UnknownResponseException("Received '$prefix' as response type");
        }
    }
}
