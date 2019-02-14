<?php

namespace Authters\ServiceBus\Contract;

interface Messager
{
    /**
     * @param $message
     * @return mixed
     */
    public function dispatch($message);
}