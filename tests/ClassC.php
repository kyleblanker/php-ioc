<?php

class ClassC
{
    private $primitive;

    public function __construct($primitive)
    {
        $this->primitive = $primitive;
    }

    public function getPrimitive()
    {
        return $this->primitive;
    }
}
