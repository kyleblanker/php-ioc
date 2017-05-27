<?php

namespace KyleBlanker\Ioc;

class Container implements ContainerInterface
{
    /**
     * container's internal instances
     * @var array
     */
    protected static $items = [];

    /**
     * calls a method or function with it's dependencies
     *
     * @param  mixed $method    name of function or closure
     * @param  array $arguments primitive arguments
     * @param  mixed $instance  object or class
     * @return mixed method/function output
     */
    public function call($method, $arguments = [], $instance = null)
    {
        if ($method instanceof \Closure) {
            return $this->resolveClosure($method, $arguments);
        }

        return $this->resolveMethod($instance, $method, $arguments);
    }

    /**
     * Resolves a method off an object
     *
     * @param  mixed $instance  instance or string of class
     * @param  string $method method to be called
     * @param  array   $arguments primitive arguments
     * @return mixed method's output
     */
    protected function resolveMethod($instance, $method, $arguments)
    {

        if (is_string($instance)) {
            $instance = $this->make($instance);
        }

        if (!method_exists($instance, $method)) {
            return false;
        }

        $reflection_method = new \ReflectionMethod($instance, $method);
        $parameters = $reflection_method->getParameters();
        $resolvedArguments = $this->resolveParameters($parameters, $arguments);

        return $reflection_method->invokeArgs($instance, $resolvedArguments);
    }

    /**
     * Resolves a closure
     *
     * @param  callable $function  function to resolve
     * @param  array   $arguments primitive arguments
     * @return mixed  callable's output
     */
    protected function resolveClosure(callable $function, $arguments)
    {
        $reflection_function = new \ReflectionFunction($function);
        $parameters = $reflection_function->getParameters();
        $resolvedArguments = $this->resolveParameters($parameters, $arguments);

        return $reflection_function->invokeArgs($resolvedArguments);
    }

    /**
     * Resolves the parameters of a method/function
     *
     * @param  array  $parameters list of paramters
     * @param  array $arguments  primitive arguments
     * @return array $dependencies the resolved dependencies of the method/function
     */
    protected function resolveParameters(array $parameters, $arguments)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            try
            {
                $dependency_class = $parameter->getClass();

                if (empty($dependency_class)) {
                    $dependencies[] = $this->resolvePrimitive($parameter, $arguments);
                    continue;
                }

                $dependency_class_name = $dependency_class->name;
            } catch (\Exception $e) {
                $class = null;

                if ($parameter->getDeclaringClass()) {
                    $class = $parameter->getDeclaringClass()->getName();
                }
                $msg = sprintf('Unable to resolve class "%s"', $class);
                throw new Exceptions\ContainerException($msg);
            }

            $dependencies[] = $this->make($dependency_class_name);
        }

        return $dependencies;
    }

    /**
     * Resolves the primitive parameters of a method/function
     *
     * @param  ReflectionParameter $parameter The parameter to resolve
     * @param  array $arguments list of primitive arguments
     * @return mixed argument that will represent that parameter
     */
    protected function resolvePrimitive(\ReflectionParameter $parameter, array &$arguments)
    {
        if (count($arguments) > 0) {
            return array_shift($arguments);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $msg = sprintf('Unable to resolve primitive parameter: "%s".', $parameter->getName());
        throw new Exceptions\ContainerException($msg);
    }

    /**
     * Attempts to inject dependencies into a new instance of the class
     *
     * @param string $class The class to create an instance of
     * @param array list of primitive data type arguments to add to class constructor
     * @return object
     */
    public function make($class, $arguments = [])
    {
        if (!class_exists($class)) {
            $msg = sprintf('Class "%s" does not exist', $class);
            throw new Exceptions\ContainerException($msg);
        }

        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return $reflection->newInstance();
        }

        $params = $constructor->getParameters();

        $resolvedArguments = $this->resolveParameters($params, $arguments);

        return $reflection->newInstanceArgs($resolvedArguments);
    }

    /**
     * Loops through array and calls set method.
     *
     * @param array $array array of instances to set.
     * @return void
     */
    public function setArray($array)
    {
        foreach ($array as $name => $instance) {
            $this->set($name, $instance);
        }
    }

    /**
     * Adds instance to Container's instances array
     *
     * @param string $name name of instance
     * @param mixed $instance content
     * @return mixed
     */
    public function set($name, $instance)
    {
        if (is_string($instance) && class_exists($instance)) {
            $instance = self::make($instance);
        }

        static::$items[$name] = $instance;

        return $instance;
    }

    /**
     * Returns entry from Container's instances array
     *
     * @param string $id Identifier of the entry to look for.
     * @return mixed
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new Exceptions\NotFoundException();
        }

        $instance = static::$items[$id];
        return $instance instanceof \Closure ? $instance() : $instance;
    }

    /**
     * Checks if container has an entry for the given identifier
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has($id)
    {
        return isset(static::$items[$id]);
    }

    /**
     * Attempts to get an instance, if none is to be found, attempt to create on
     * This will only work with classes
     *
     * @param string $class name of class
     * @return mixed
     */
    public function getOrSet($class)
    {
        try {
            $instance = $this->get($class);
        } catch (Exceptions\NotFoundException $e) {
            $this->set($class, $class);
            $instance = $this->get($class);
        }

        return $instance;

    }
}
