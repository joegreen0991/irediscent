<?php namespace Irediscent\Formatter;

class ScanResponse extends ArrayResponse
{
    public function format($command, $response)
    {
        $callback = strtolower($command) === 'zscan' ? 'floatval' : null;

        if (is_array($response))
        {
            $response[1] = parent::formatArray($command, $response, $callback);
        }

        return $response;
    }
}
