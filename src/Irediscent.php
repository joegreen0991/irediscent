<?php
use Irediscent\Connection\ConnectionInterface;
use Irediscent\Connection\Factory;


class Irediscent {

    /**
     * @var \Irediscent\Connection\ConnectionInterface;
     */
    protected $connection;

    /**
     * Flag indicating whether or not commands are being pipelined
     * @var boolean
     * @access private
     */
    private $pipelined = false;

    /**
     * The queue of commands to be sent to the Redis server
     * @var array
     * @access private
     */
    private $queue = array();

    /**
     * @var array
     */
    private $defaultOptions = array(
        'database' => 0,
        'timeout' => 5
    );

    /**
     * @param string|ConnectionInterface $connection The data source name of the Redis server
     * @param string $password
     * @param array $options
     */
    public function __construct($connection = null, $password = null, array $options = array())
    {
        $this->connection = $connection instanceof ConnectionInterface ? $connection : Factory::make($connection);

        $this->password = $password;

        $this->options = $options + $this->defaultOptions;

        $this->connect();
    }

    /**
     * Connect to the database
     */
    public function connect()
    {
        $this->connection->connect();

        if($this->password)
        {
            $this->auth($this->password);
        }

        if($this->options['database'])
        {
            $this->select($this->options['database']);
        }

        return $this;
    }

    /**
     * Disconnect from the database
     */
    public function disconnect()
    {
        $this->connection->disconnect();

        return $this;
    }

    /**
     * Disconnect and reconnect to the database
     */
    public function reconnect()
    {
        return $this->disconnect()->connect();
    }

    /**
     * Returns the Redisent instance ready for pipelining.
     * Redis commands can now be chained, and the array of the responses will be returned when {@link uncork} is called.
     * @see uncork
     * @access public
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
     * Returns the Redisent instance ready for pipelining.
     * Redis commands can now be chained, and the array of the responses will be returned when {@link uncork} is called.
     * @see uncork
     * @access public
     */
    public function multiExec(\Closure $callback) {

        $this->multi();

        $callback($this);

        return $this->exec();
    }

    /**
     * Returns the Redisent instance ready for pipelining.
     * Redis commands can now be chained, and the array of the responses will be returned when {@link uncork} is called.
     * @see uncork
     * @access public
     */
    public function multi() {

        $this->pipelined = true;

        return $this->executeCommand('multi');
    }

    /**
     * Flushes the commands in the pipeline queue to Redis and returns the responses.
     * @see pipeline
     * @access public
     */
    public function exec()
    {
        $this->pipelined = true;

        return $this->executeCommand('exec')->uncork();
    }

    public function uncork()
    {
        $responses = $this->connection->multiWrite($this->queue);

        $this->pipelined = false;

        $this->queue = array();

        return $responses;
    }

    protected function executeCommand($name, array $args = array())
    {
        array_unshift($args, strtoupper($name));

        if ($this->pipelined)
        {
            $this->queue[] = $args;
            return $this;
        } else
        {
            return $this->connection->write($args);
        }
    }
    
    public function execute($command, $_args = array())
    {
        return $this->executeCommand($command, is_array($_args) ? $_args : array_slice(func_get_args(),1));
    }

    public function __call($name, $args)
    {
        return $this->executeCommand($name, $args);
    }

}
