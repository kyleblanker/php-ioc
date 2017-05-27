<?php

include 'ClassA.php';
include 'ClassB.php';
include 'ClassC.php';
include 'ClassD.php';

use KyleBlanker\Ioc\Container;
use KyleBlanker\Ioc\Exceptions\ContainerException;
use KyleBlanker\Ioc\Exceptions\NotFoundException;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    private function getContainer()
    {
        return new Container();
    }

    public function testClass()
    {
        $container = $this->getContainer();
        $instance = $container->make(ClassD::class);
        $this->assertTrue($instance instanceof ClassD);
    }

    public function testClassWithPrimitives()
    {
        $container = $this->getContainer();
        $instance = $container->make(ClassC::class, ['value']);
        $this->assertTrue($instance instanceof ClassC);
        $this->assertEquals($instance->getPrimitive(), 'value');
    }

    public function testClassWithDependencies()
    {
        $container = $this->getContainer();
        $instance = $container->make(ClassB::class, ['value']);
        $this->assertTrue($instance instanceof ClassB);
        $this->assertTrue($instance->getD() instanceof ClassD);
    }

    public function testClassWithMixedParameters()
    {
        $container = $this->getContainer();
        $instance = $container->make(ClassA::class, ['value']);
        $this->assertTrue($instance instanceof ClassA);
        $this->assertEquals($instance->getPrimitive(), 'value');
        $this->assertTrue($instance->getB() instanceof ClassB);
    }

    public function testClosure()
    {

        $closure = function (ClassD $d) {
            return $d;
        };

        $container = $this->getContainer();

        $instance = $container->call($closure);
        $this->assertTrue($instance instanceof ClassD);
    }

    public function testMethod()
    {
        $container = $this->getContainer();

        $value = $container->call('getD', [], ClassB::class);
        $this->assertTrue($value instanceof ClassD);
    }

    public function testInvalidClass()
    {
        $this->expectException(ContainerException::class);
        $container = $this->getContainer();
        $instance = $container->make(ClassE::class);
    }

    public function testSetMethod()
    {
        $container = $this->getContainer();
        $container->set('entry', ClassB::class);

        $this->assertTrue($container->get('entry') instanceof ClassB);
    }

    public function testSetArray()
    {
        $container = $this->getContainer();

        $container->setArray([
            'entry1' => ClassB::class,
            'entry2' => ClassD::class,
            'entry3' => 'just a string',
        ]);

        $this->assertTrue($container->get('entry1') instanceof ClassB);
        $this->assertTrue($container->get('entry2') instanceof ClassD);
        $this->assertEquals($container->get('entry3'), 'just a string');
    }

    public function testNotFoundException()
    {
        $this->expectException(NotFoundException::class);
        $container = $this->getContainer();
        $instance = $container->get('not_a_valid_entry');
    }
}
