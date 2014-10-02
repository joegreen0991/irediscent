<?php
use Irediscent\Connection\ConnectionInterface;
use Irediscent\Connection\SocketConnection;
use Irediscent\Exception\RedisException;

class Irediscent {

    /**
     * @var \Irediscent\Connection\ConnectionInterface;
     */
    protected $connection;

    /**
     * Flag indicating whether or not commands are being pipelined
     * @var bool
     */
    private $pipelined = false;

    /**
     * The queue of commands to be sent to the Redis server when pipelining
     * @var array
     */
    private $queue = array();

    /**
     * The password to authenticate with on connection
     * @var null|string
     */
    private $password;

    /**
     * The Database index to select on connection
     * @var null|int
     */
    private $database;

    /**
     * @var array
     */
    private $commandSha = array();

    /**
     * @var array
     */
    private $formatters = array(
        'hgetall'       => 'Irediscent\Formatter\ArrayResponse',
        'config'        => 'Irediscent\Formatter\ArrayResponse',
        'sentinel'      => 'Irediscent\Formatter\SentinelResponse',
        'scan'          => 'Irediscent\Formatter\ScanResponse',
        'hscan'         => 'Irediscent\Formatter\ScanResponse',
        'zscan'         => 'Irediscent\Formatter\ScanResponse',
    );

    private $formattersCache = array();

    /**
     * @param string|ConnectionInterface $connection The data source name of the Redis server
     * @param string $password
     * @param array $options
     */
    public function __construct($connection = null, $password = null, $database = null)
    {
        $this->connection = $this->resolveConnection($connection);

        $this->password = $password;

        $this->database = $database;

        $this->connect();
    }

    protected function resolveConnection($connection)
    {
        return $connection instanceof ConnectionInterface ? $connection : \Irediscent\Connection\Factory::make($connection);
    }

    /**
     * Connect to the redis server
     * @return $this
     */
    public function connect()
    {
        $this->connection->connect();

        if($this->password)
        {
            $this->auth($this->password);
        }

        if($this->database)
        {
            $this->select($this->database);
        }

        return $this;
    }

    /**
     * Disconnect from the redis server
     * @return $this
     */
    public function disconnect()
    {
        $this->connection->disconnect();

        return $this;
    }

    /**
     * Drop the connection to the server and recreate
     * @return $this
     */
    public function reconnect()
    {
        return $this->disconnect()->connect();
    }

    /**
     * @return $this
     */
    public function isConnected()
    {
        $this->connection->isConnected();

        return $this;
    }

    /**
     * Enable pipelined execution. All subsequent commands will be stored until `uncork()` is called, at which point
     * the commands will be sent to the server in a single write action.
     *
     * Optionally provide a callback to execute, after which `uncork` will be called automatically
     *
     * @param callable $callback
     * @return $this|array
     */
    public function pipeline(\Closure $callback = null) {

        $this->pipelined = true;

        if($callback)
        {
            $callback($this);

            return $this->uncork();
        }

        return $this;
    }

    /**
     * Commands will be pipelined, with the addition of a call to `multi` before and `exec` after the given callback
     * @see pipeline
     * @param callable $callback
     * @return array
     */
    public function multiExec(\Closure $callback) {

        return $this->pipeline(function() use($callback){
            $this->multi();
            $callback($this);
            $this->exec();
        });
    }

    /**
     * Flush the pipelined command queue to the server and return the responses.
     *
     * @return array
     */
    public function uncork()
    {
        if(!$this->isConnected())
        {
            $this->connect();
        }

        $responses = $this->connection->multiWrite($this->queue);

        $this->pipelined = false;

        $this->queue = array();

        return $responses;
    }

    /**
     * Execute an arbitrary command with an array of arguments
     *
     * @param $command
     * @param array $_args
     * @return $this|Irediscent
     */
    public function execute($command, $_args = array())
    {
        return $this->executeCommand($command, is_array($_args) ? $_args : array_slice(func_get_args(),1));
    }

    /**
     * @param $name
     * @param array $args
     * @return $this
     */
    public function executeCommand($name, array $args = array())
    {
        if(!$this->isConnected())
        {
            $this->connect();
        }

        array_unshift($args, strtoupper($name));

        if ($this->pipelined)
        {
            $this->queue[] = $args;

            return $this;
        }

        return $this->format($name, $this->connection->write($args));
    }

    protected function format($command, $response)
    {
        if(isset($this->formatters[$command]))
        {
            if(!isset($this->formattersCache[$command]))
            {
                $this->formattersCache[$command] = new $this->formatters[$command];
            }

            return $this->formattersCache[$command]->format($command, $response);
        }

        return $response;
    }

    /**
     * Switch any eval to an evaSha followed by an eval
     *
     * The only time this should not happen is when in a pipeline so we check for that first
     *
     * @param $arguments
     * @return Irediscent
     * @throws Exception
     */
    protected function smartEval($arguments)
    {
        if(!$this->pipelined)
        {
            $evalCmd = $arguments[0];

            isset($this->commandSha[$evalCmd]) or $this->commandSha[$evalCmd] = sha1($evalCmd);

            $arguments[0] = $this->commandSha[$evalCmd];

            try {
                return $this->executeCommand('evalSha', $arguments);
            }
            catch(RedisException $e)
            {
                if(strpos($e->getMessage(), 'NOSCRIPT ') !== 0)
                {
                    throw $e;
                }
            }

            $arguments[0] = $evalCmd;
        }

        return $this->executeCommand('eval', $arguments);
    }

    /**
     * Magic helper allowing $this->hset(), $this->set() etc... style commands
     *
     * @param $name
     * @param $args
     * @return Irediscent
     */
    public function __call($name, $args)
    {
        if(strtolower($name) === 'eval')
        {
            return $this->smartEval($args);
        }

        return $this->executeCommand($name, $args);
    }

}
