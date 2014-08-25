<?php namespace Irediscent\Connection;

use Irediscent\Connection\Serializer\IRedis;
use Irediscent\Exception\TransmissionException;

class IredisSocketConnection extends SocketConnection {

    /**
     * @var IRedis
     */
    protected $serializer;

    public function __construct($dsn = null, $timeout = self::DEFAULT_TIMEOUT)
    {
        $this->serializer = new IRedis();

        parent::__construct($dsn, $timeout);
    }

    protected function serialize($data)
    {
        return $this->serializer->serialize($data);
    }

    protected function readResponse()
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
