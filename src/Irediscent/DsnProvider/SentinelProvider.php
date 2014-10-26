<?php namespace Irediscent\DsnProvider;

use Irediscent\Connection\Factory;
use Irediscent\Connection\ConnectionInterface;
use Irediscent\Connection\SocketConnection;
use Irediscent\Exception\ConnectionException;
use Irediscent\Exception\NoSentinelsException;

class SentinelProvider implements DsnProviderInterface
{
    /**
     * The length of time to wait before trying the next sentinel
     */
    const SENTINEL_TIMEOUT = 3.0;

    /**
     * @var array
     */
    protected $sentinels;

    /**
     * @var string
     */
    protected $mastername;

    /**
     * @param array $sentinels
     * @param $mastername
     */
    public function __construct(array $sentinels, $mastername)
    {
        $this->sentinels = $sentinels;

        $this->mastername = $mastername;
    }

    /**
     * @param $command
     * @return mixed
     * @throws NoSentinelsException
     */
    protected function runCommand($command)
    {
        foreach($this->sentinels as $sentinel)
        {
            $connection = $sentinel instanceof ConnectionInterface ? $sentinel : new SocketConnection($sentinel, null, self::SENTINEL_TIMEOUT);

            try
            {
                $connection->connect();
            }
            catch (ConnectionException $e)
            {
                continue;
            }

            $response = $connection->write($command);

            $connection->write(array('QUIT'));

            return $response;
        }

        throw new NoSentinelsException("Could not connect to any sentinel");
    }

    /**
     * @return array
     * @throws NoSentinelsException
     */
    public function getMasterDsn()
    {
        $response = $this->runCommand(array('SENTINEL', 'get-master-addr-by-name', $this->mastername));

        return array(
            'host' => $response[0],
            'port' => $response[1]
        );
    }

    /**
     * @return mixed
     * @throws NoSentinelsException
     */
    public function getSlavesDsn()
    {
        $slaves = $this->runCommand(array('SENTINEL', 'slaves', $this->mastername));

        foreach($slaves as $i => $slave)
        {
            $slaves[$i] =  array(
                'host' => $slave[3],
                'port' => $slave[5]
            );
        }

        return $slaves;
    }
}