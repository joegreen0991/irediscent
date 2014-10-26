<?php namespace Irediscent\DsnProvider;


interface DsnProviderInterface
{

    public function getMasterDsn();

    public function getSlavesDsn();

}
