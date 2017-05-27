<?php

class ClassB
{
    private $c;

    public function __construct(ClassD $d)
    {
        $this->d = $d;
    }

    public function getD()
    {
        return $this->d;
    }
}
