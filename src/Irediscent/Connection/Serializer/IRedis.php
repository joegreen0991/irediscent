<?php namespace Irediscent\Connection\Serializer;

use Irediscent\Exception\RedisException;
use Irediscent\Exception\UnknownResponseException;

class IRedis implements  SerializerInterface {

    private $reader;

    public function __construct()
    {
        if (!function_exists('phpiredis_reader_create')) {
            throw new NotSupportedException(
                'The phpiredis extension must be loaded in order to be able to use this serializer'
            );
        }

        $this->initializeReader();
    }

    /**
     * Initializes the protocol reader resource.
     */
    protected function initializeReader()
    {
        $this->reader = phpiredis_reader_create();

        phpiredis_reader_set_status_handler($this->reader, function ($payload) {
            return $payload === 'OK' ? true : $payload;
        });

        phpiredis_reader_set_error_handler($this->reader, function ($errorMessage) {
            throw new RedisException($errorMessage);
        });
    }

    public function serialize($data)
    {
        return phpiredis_format_command($data);
    }

    public function read($response)
    {
        phpiredis_reader_feed($this->reader, $response);

        switch(phpiredis_reader_get_state($this->reader))
        {
            case PHPIREDIS_READER_STATE_INCOMPLETE:
                return false;
            case PHPIREDIS_READER_STATE_COMPLETE:
                return phpiredis_reader_get_reply($this->reader);
        }

        throw new RedisException(phpiredis_reader_get_error($this->reader));
    }

    public function __destruct()
    {
        phpiredis_reader_destroy($this->reader);
    }
}
