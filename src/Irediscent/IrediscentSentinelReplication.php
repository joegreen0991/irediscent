<?php namespace Irediscent;

use Irediscent\DsnProvider\SentinelProvider;
use Irediscent\Exception\RedisException;

class IrediscentSentinelReplication extends \Irediscent {

    private $connectionRetries = 5;

    /**
     * @param array $sentinels
     * @param null|string $mastername
     * @param null $password
     * @param null $database
     */
    public function __construct(array $sentinels, $mastername, $password = null, $database = null)
    {
        parent::__construct(new SentinelProvider($sentinels, $mastername), $password, $database);
    }

    /**
     * Augmented functionality - If the master/slave has changed, just reconnect and try the request again.
     * The sentinels should give us the correct connection parameters for the new master
     *
     * @param $name
     * @param array $args
     * @return $this
     * @throws RedisException
     */
    protected function executeCommand($name, array $args = array())
    {
        $retry = $this->connectionRetries; // Avoid infinite loop

        while($retry--)
        {
            try {
                return parent::executeCommand($name, $args);
            } catch (RedisException $e) {
                if (!$this->exceptionIsReadonlySlave($e)) {
                    throw $e;
                }

                $this->reconnect();
            }
        }

        throw new RedisException("Could not reconnect to master after $this->connectionRetries attempts");
    }

    /**
     * @param RedisException $e
     * @return bool
     */
    private function exceptionIsReadonlySlave(RedisException $e)
    {
        return strpos($e->getMessage(), 'READONLY You can\'t write against a read only slave') !== 0;
    }

}
