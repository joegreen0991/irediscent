<?php namespace Irediscent;
use Irediscent\Connection\ConnectionInterface;
use Irediscent\Connection\SocketConnection;
use Irediscent\DsnProvider\SentinelProvider;
use Irediscent\Exception\RedisException;

class IrediscentSentinelReplication extends \Irediscent {

    /**
     * @param string|ConnectionInterface $connection The data source name of the Redis server
     * @param string $password
     * @param array $options
     */
    public function __construct(array $sentinels, $mastername, $password = null, $database = null)
    {
        $connection = new SocketConnection(new SentinelProvider($sentinels, $mastername));

        parent::__construct($connection, $password, $database);
    }

    /**
     * Augmented functionality - If the master/slave has changed, just reconnect and try the request again.
     * The sentinels should give us the correct connection parameters for the new master
     *
     * @param $name
     * @param array $args
     * @return $this
     */
    protected function executeCommand($name, array $args = array())
    {
        try
        {
            return parent::executeCommand($name, $args);
        }
        catch(RedisException $e)
        {
            if(strpos($e->getMessage(), 'READONLY You can\'t write against a read only slave') !== 0)
            {
                throw $e;
            }

            $this->reconnect();

            return parent::executeCommand($name, $args);
        }
    }

}
