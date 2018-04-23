<?php

namespace Dhii\Collection\UnitTest;

use Dhii\Iterator\IterationInterface;
use Iterator;
use Xpmock\TestCase;
use Dhii\Collection\AbstractBaseMap as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Exception as RootException;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class AbstractBaseMapTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Collection\AbstractBaseMap';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array|null $methods The methods to mock.
     *
     * @return TestSubject|MockObject The new instance.
     */
    public function createInstance($methods = [], $constructorArgs = [], $disableOriginalConstructor = false)
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->setConstructorArgs($constructorArgs);
        $disableOriginalConstructor && $builder->disableOriginalConstructor();
        $mock = $builder->getMock();

        $mock->method('__')
            ->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string $className      Name of the class for the mock to extend.
     * @param string $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return object The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->getMockForAbstractClass($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Iterator.
     *
     * @since [*next-version*]
     *
     * @param array|null $methods The methods to mock.
     *
     * @return Iterator|MockObject The new iterator instance.
     */
    public function createIterator($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            'current',
            'key',
            'valid',
            'next',
            'rewind',
        ]);

        $mock = $this->getMockBuilder('Iterator')
            ->setMethods($methods)
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new iteration.
     *
     * @since [*next-version*]
     *
     * @param array|null $methods The methods to mock.
     *
     * @return IterationInterface|MockObject The new iteration instance.
     */
    public function createIteration($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            'getKey',
            'getvalue',
        ]);

        $mock = $this->getMockBuilder('Dhii\Iterator\IterationInterface')
            ->setMethods($methods)
            ->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance([], [], true);
        $this->assertInstanceOf(static::TEST_SUBJECT_CLASSNAME, $subject, 'A valid instance of the test subject could not be created.');
    }

    /**
     * Tests that `_loop()` works as expected.
     *
     * @since [*next-version*]
     */
    public function testLoop()
    {
        $key = uniqid('key');
        $value = uniqid('val');
        $iterator = $this->createIterator(['next']);
        $iteration = $this->createIteration();
        $subject = $this->createInstance(['_getIterator', '_calculateKey', '_calculateValue', '_createIteration']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getIterator')
            ->will($this->returnValue($iterator));

        $iterator->expects($this->exactly(1))
            ->method('next');

        $subject->expects($this->exactly(1))
            ->method('_calculateKey')
            ->with($iterator)
            ->will($this->returnValue($key));
        $subject->expects($this->exactly(1))
            ->method('_calculateValue')
            ->with($iterator)
            ->will($this->returnValue($value));
        $subject->expects($this->exactly(1))
            ->method('_createIteration')
            ->with($key, $value)
            ->will($this->returnValue($iteration));

        $result = $_subject->_loop();
        $this->assertSame($iteration, $result, 'Wrong iteration retrieved');
    }

    /**
     * Tests that `_calculateKey()` works as expected when the iterator has a valid key.
     *
     * @since [*next-version*]
     */
    public function testCalculateKeyExplicit()
    {
        $key = uniqid('key');
        $iterator = $this->createIterator(['valid', 'key']);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $iterator->expects($this->exactly(1))
            ->method('valid')
            ->will($this->returnValue(true));
        $iterator->expects($this->exactly(1))
            ->method('key')
            ->will($this->returnValue($key));

        $result = $_subject->_calculateKey($iterator);
        $this->assertEquals($key, $result, 'Calculated key is wrong');
    }

    /**
     * Tests that `_calculateKey()` works as expected when the iterator does not have a valid key.
     *
     * @since [*next-version*]
     */
    public function testCalculateKeyDefault()
    {
        $key = null;
        $iterator = $this->createIterator(['valid']);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $iterator->expects($this->exactly(1))
            ->method('valid')
            ->will($this->returnValue(false));

        $result = $_subject->_calculateKey($iterator);
        $this->assertEquals($key, $result, 'Calculated key is wrong');
    }

    /**
     * Tests that `_calculateValue()` works as expected when the iterator has a valid value.
     *
     * @since [*next-version*]
     */
    public function testCalculateValueExplicit()
    {
        $value = uniqid('val');
        $iterator = $this->createIterator(['valid', 'current']);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $iterator->expects($this->exactly(1))
            ->method('valid')
            ->will($this->returnValue(true));
        $iterator->expects($this->exactly(1))
            ->method('current')
            ->will($this->returnValue($value));

        $result = $_subject->_calculateValue($iterator);
        $this->assertEquals($value, $result, 'Calculated key is wrong');
    }

    /**
     * Tests that `_calculateValue()` works as expected when the iterator has a valid value.
     *
     * @since [*next-version*]
     */
    public function testCalculateValueDefault()
    {
        $value = null;
        $iterator = $this->createIterator(['valid']);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $iterator->expects($this->exactly(1))
            ->method('valid')
            ->will($this->returnValue(false));

        $result = $_subject->_calculateKey($iterator);
        $this->assertEquals($value, $result, 'Calculated key is wrong');
    }
}
