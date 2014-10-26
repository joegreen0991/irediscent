<?php namespace Irediscent\Formatter;

class ArrayResponse implements FormatterInterface
{
    protected function formatArray($response, callable $callback = null)
    {
        if (is_array($response)) {
            $data = array();

            for ($i = 0, $len = count($response); $i < $len; $i += 2) {
                $data[$response[$i]] = $callback ? $callback($response[$i + 1]) : $response[$i + 1];
            }
            return $data;
        }

        return $response;
    }

    public function format($command, $response)
    {
        return $this->formatArray($response);
    }
}
