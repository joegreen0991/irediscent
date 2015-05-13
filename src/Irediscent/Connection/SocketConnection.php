<?php namespace Irediscent\Connection;

use Irediscent\Connection\Util\SocketObject;
use Irediscent\Exception\ConnectionException;
use Irediscent\Exception\RedisException;
use Irediscent\Exception\TransmissionException;
use Irediscent\Exception\UnknownResponseException;

class SocketConnection extends ConnectionAbstract {

    const DEFAULT_TIMEOUT = 5.0;

    const DEFAULT_RW_TIMEOUT = 5.0;

    const CRLF = "\r\n";

    protected $timeout;

    protected $socket;

    /**
     * Socket connection to the Redis server
     * @var resource
     * @access private
     */
    protected $redis;

    public function __construct($dsn = null, $timeout = null, $readWriteTimeout = null)
    {
        $this->timeout = is_null($timeout) ? self::DEFAULT_TIMEOUT : (float)$timeout;

        $this->readWriteTimeout = is_null($readWriteTimeout) ? self::DEFAULT_RW_TIMEOUT : (float)$readWriteTimeout;

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

        $this->redis = $this->socket->open($connection['host'], $connection['port'], $errno, $errstr, $this->timeout, $this->readWriteTimeout);

        if ($this->redis === false)
        {
            throw new ConnectionException("Could not connect to {$connection['host']}:{$connection['port']}; {$errno} - {$errstr}");
        }
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->redis !== null;
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
        $chunk = $this->socket->gets($this->redis, 512);

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
                $size = (int) $payload;

                if ($size === -1)
                {
                    return null;
                }

                $response = '';
                $bytesLeft = ($size + 2);

                do {
                    $chunk = $this->socket->read($this->redis, min($bytesLeft, 4096));

                    if ($chunk === false || $chunk === '')
                    {
                        throw new TransmissionException("Failed to read inline data packet from server");
                    }

                    $response .= $chunk;
                    $bytesLeft -= strlen($chunk);

                } while ($bytesLeft > 0);

                return substr($response, 0, -2);
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
