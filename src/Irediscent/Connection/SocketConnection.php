<?php namespace Irediscent\Connection;

use Irediscent\Connection\Serializer\Factory;
use Irediscent\Connection\Serializer\SerializerInterface;
use Irediscent\Connection\Util\SocketObject;
use Irediscent\Exception\ConnectionException;
use Irediscent\Exception\TransmissionException;

class SocketConnection extends ConnectionAbstract {

    const DEFAULT_TIMEOUT = 5.0;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    protected $timeout;

    protected $socket;

    public function __construct($dsn = null, SerializerInterface $serializer = null, $timeout = self::DEFAULT_TIMEOUT)
    {
        $this->timeout = (float)$timeout;

        $this->serializer = $serializer ?: Factory::make();

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
        $command = $this->serializer->serialize($data);

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
        do {
            $chunk = $this->socket->gets($this->redis, 4096);

            if ($chunk === false || $chunk === '' || $chunk === null)
            {
                throw new TransmissionException('Failed to read response from stream');
            }

        }while(($result = $this->serializer->read($chunk)) === false);

        return $result;
    }
}
