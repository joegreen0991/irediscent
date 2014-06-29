<?php namespace Irediscent\DsnProvider;


class StaticProvider implements DsnProviderInterface
{
    const DEFAULT_HOST = 'localhost';

    const DEFAULT_PORT = 6379;

    protected $host = self::DEFAULT_HOST;

    protected $port = self::DEFAULT_PORT;

    protected $slaves = array();

    public function __construct($dsn, array $slaves = array())
    {
        is_array($dsn) or $dsn = $this->parseDsn($dsn);

        empty($dsn['host']) or $this->host = $dsn['host'];

        empty($dsn['port']) or $this->port = $dsn['port'];

        foreach($slaves as $i => $slave)
        {
            is_array($slave) or $slave = $this->parseDsn($slave);

            $this->slaves[] = array(
                'host' => empty($slave['host']) ? self::DEFAULT_HOST : $slave['host'],
                'port' => empty($slave['port']) ? self::DEFAULT_PORT : $slave['port'],
            );
        }
    }

    protected function parseDsn($dsn)
    {
        $parts = explode(':', $dsn, 2);

        $return = array(
            'host' => $parts[0]
        );

        isset($parts[1]) and $return['port'] = $parts[1];

        return $return;
    }

    public function getMasterDsn()
    {
        return array(
            'host' => $this->host,
            'port' => $this->port
        );
    }

    public function getSlavesDsn()
    {
        return $this->slaves;
    }
}