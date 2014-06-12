<?php namespace Irediscent\Connection;

use Irediscent\Exception\ConnectionException;
use Irediscent\Exception\RedisException;
use Irediscent\Exception\UnknownResponseException;

class Redisent extends ConnectionAbstract {

    public function connect()
    {
        $timeout = ini_get("default_socket_timeout");

        $this->redis = @fsockopen($this->host, $this->port, $errno, $errstr, $timeout);

        if ($this->redis === false) {
            throw new \Exception("{$errno} - {$errstr}");
        }
    }

    public function disconnect()
    {
        fclose($this->redis);

        $this->redis = null;
    }

    public function write($data)
    {
        $this->writeRaw($data);

        return $this->readResponse();
    }

    public function multiWrite($data)
    {
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
        $crlf = "\r\n";
        $command = '*' . count($data) . $crlf;
        foreach ($data as $arg) {
            $command .= '$' . strlen($arg) . $crlf . $arg . $crlf;
        }

        for ($written = 0; $written < strlen($command); $written += $fwrite)
        {
            $fwrite = fwrite($this->redis, substr($command, $written));

            if ($fwrite === FALSE || $fwrite <= 0)
            {
                throw new \Exception('Failed to write entire command to stream');
            }
        }
    }

    private function readResponse() {
        /* Parse the response based on the reply identifier */
        $reply = trim(fgets($this->redis, 512));
        switch (substr($reply, 0, 1)) {
            /* Error reply */
            case '-':
                throw new RedisException(trim(substr($reply, 4)));
                break;
            /* Inline reply */
            case '+':
                $response = substr(trim($reply), 1);
                if ($response === 'OK') {
                    $response = TRUE;
                }
                break;
            /* Bulk reply */
            case '$':
                $response = NULL;
                if ($reply == '$-1') {
                    break;
                }
                $read = 0;
                $size = intval(substr($reply, 1));
                if ($size > 0) {
                    do {
                        $block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
                        $r = fread($this->redis, $block_size);
                        if ($r === FALSE) {
                            throw new ConnectionException('Failed to read response from stream');
                        } else {
                            $read += strlen($r);
                            $response .= $r;
                        }
                    } while ($read < $size);
                }
                fread($this->redis, 2); /* discard crlf */
                break;
            /* Multi-bulk reply */
            case '*':
                $count = intval(substr($reply, 1));
                if ($count == '-1') {
                    return NULL;
                }
                $response = array();
                for ($i = 0; $i < $count; $i++) {
                    $response[] = $this->readResponse();
                }
                break;
            /* Integer reply */
            case ':':
                $response = intval(substr(trim($reply), 1));
                break;
            default:
                throw new UnknownResponseException("Unknown response: {$reply}");
                break;
        }
        /* Party on */
        return $response;
    }
}
