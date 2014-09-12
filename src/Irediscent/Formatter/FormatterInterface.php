<?php namespace Irediscent\Formatter;

interface FormatterInterface
{
    public function format($command, $response);
}