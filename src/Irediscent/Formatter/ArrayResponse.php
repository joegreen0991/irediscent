<?php namespace Irediscent\Formatter;

class ArrayResponse implements FormatterInterface
{
    public function format($response)
    {
        $data = array();

        for($i = 0, $len = count($response); $i < $len; $i += 2)
        {
            $data[$response[$i]] = $response[$i + 1];
        }

        return $data;
    }
}