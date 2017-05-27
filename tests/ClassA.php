<?php

class ClassA
{
    private $b;
    private $primitive;

    public function __construct(ClassB $b, $primitive)
    {
        $this->b = $b;
        $this->primitive = $primitive;
    }

    public function getB()
    {
        return $this->b;
    }

    public function getPrimitive()
    {
        return $this->primitive;
    }
}
