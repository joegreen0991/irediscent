<?php namespace Irediscent\Formatter;

class SentinelResponse implements FormatterInterface
{
    public function format($command, $response)
    {
        switch (strtolower($command)) {
            case 'masters':
            case 'slaves':
                return $this->processMastersOrSlaves($command);

            default:
                return $command;
        }
    }

    /**
     * Returns a processed response to SENTINEL MASTERS or SENTINEL SLAVES.
     *
     * @param array $servers List of Redis servers.
     *
     * @return array
     */
    protected function processMastersOrSlaves(array $servers)
    {
        foreach ($servers as $idx => $node) {
            $processed = array();
            $count = count($node);

            for ($i = 0; $i < $count; $i++) {
                $processed[$node[$i]] = $node[++$i];
            }

            $servers[$idx] = $processed;
        }

        return $servers;
    }
}