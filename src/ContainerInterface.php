<?php

namespace KyleBlanker\Ioc;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{

    /**
     * calls a method or function with it's dependencies
     *
     * @param  mixed $method    name of function or closure
     * @param  array $arguments primitive arguments
     * @param  mixed $instance  object or class
     * @return mixed method/function output
     */
    public function call($method, $arguments = [], $instance = null);

    /**
     * Attempts to inject dependencies into a new instance of the class
     *
     * @param string $class The class to create an instance of
     * @param array list of primitive data type arguments to add to class constructor
     * @return object
     */
    public function make($class, $arguments = []);

    /**
     * Loops through array and calls set method.
     *
     * @param array $array array of instances to set.
     * @return void
     */
    public function setArray($array);

    /**
     * Adds instance to Container's instances array
     *
     * @param string $name name of instance
     * @param mixed $instance content
     * @return mixed
     */
    public function set($name, $instance);

    /**
     * Attempts to get an instance, if none is to be found, attempt to create on
     * This will only work with classes
     *
     * @param string $class name of class
     * @return mixed
     */
    public function getOrSet($class);
}
