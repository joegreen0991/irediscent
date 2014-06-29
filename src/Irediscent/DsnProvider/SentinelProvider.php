<?php namespace Irediscent\DsnProvider;

use Irediscent\Connection\Factory;
use Irediscent\Connection\ConnectionInterface;
use Irediscent\Exception\ConnectionException;
use Irediscent\Exception\NoSentinelsException;

class SentinelProvider implements DsnProviderInterface
{
    protected $sentinels;

    protected $mastername;

    public function __construct(array $sentinels, $mastername)
    {
        $this->sentinels = $sentinels;

        $this->mastername = $mastername;
    }

    protected function runCommand($command)
    {
        foreach($this->sentinels as $sentinel)
        {
            $connection = $sentinel instanceof ConnectionInterface ? $sentinel : Factory::make($sentinel);

            try
            {
                $connection->connect();
            }
            catch (ConnectionException $e)
            {
                continue;
            }

            $response = $connection->write($command);

            $connection->write("QUIT");

            return $response;
        }

        throw new NoSentinelsException("Could not connect to any sentinel");
    }

    public function getMasterDsn()
    {
        $response = $this->runCommand('SENTINEL get-master-addr-by-name ' . $this->mastername);

        return array(
            'host' => $response[0],
            'port' => $response[1]
        );
    }

    public function getSlavesDsn()
    {
        $slaves = $this->runCommand('SENTINEL slaves ' . $this->mastername);

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