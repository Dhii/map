<?php

namespace Dhii\Collection\UnitTest;

use ArrayObject;
use Dhii\Collection\IteratorAwareTrait as TestSubject;
use InvalidArgumentException;
use Iterator;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class IteratorAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Collection\IteratorAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->getMockForTrait();

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
     * @return MockBuilder The builder for a mock of an object that extends and implements
     *                     the specified class and interfaces.
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
     * @return RootException The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Invalid Argument exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return InvalidArgumentException The new exception.
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new iterator mock.
     *
     * @since [*next-version*]
     *
     * @param array $elements
     *
     * @return MockObject|Iterator The new iterator mock.
     */
    public function createIterator($elements = [])
    {
        $mock = $this->getMockBuilder('Iterator')
            ->setConstructorArgs([$elements])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new store mock.
     *
     * @since [*next-version*]
     *
     * @param array      $data    The data for the store.
     * @param array|null $methods Names of methods to mock.
     *
     * @return ArrayObject|MockObject The new store mock.
     */
    public function createStore($data = [], $methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $builder = $this->getMockBuilder('ArrayObject')
            ->setConstructorArgs([$data]);
        $builder->setMethods($methods);
        $mock = $builder->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Test that `_getIterator()` works as expected with an iterator set.
     *
     * @since [*next-version*]
     */
    public function testGetIteratorSet()
    {
        $iterator = $this->createIterator();
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $_subject->iterator = $iterator;
        $result = $_subject->_getIterator();
        $this->assertSame($iterator, $result, 'Wrong iterator retrieved');
    }

    /**
     * Test that `_getIterator()` works as expected with an iterator not set.
     *
     * @since [*next-version*]
     */
    public function testGetIteratorNotSet()
    {
        $store = $this->createStore();
        $iterator = $store->getIterator();
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getDataStore')
            ->will($this->returnValue($store));
        $subject->expects($this->exactly(1))
            ->method('_resolveIterator')
            ->with($store)
            ->will($this->returnValue($iterator));

        $_subject->iterator = null;
        $result = $_subject->_getIterator();
        $this->assertSame($iterator, $result, 'Wrong iterator retrieved');
    }

    /**
     * Test that `_setIterator()` works as expected when an `Iterator` is given.
     *
     * @since [*next-version*]
     */
    public function testSetIterator()
    {
        $iterator = $this->createIterator();
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $_subject->iterator = null;
        $_subject->_setIterator($iterator);
        $result = $_subject->iterator;
        $this->assertSame($iterator, $result, 'Wrong iterator retrieved');
    }

    /**
     * Test that `_setIterator()` works as expected when `null` is given.
     *
     * @since [*next-version*]
     */
    public function testSetIteratorNull()
    {
        $iterator = null;
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $_subject->iterator = uniqid('iterator');
        $_subject->_setIterator($iterator);
        $result = $_subject->iterator;
        $this->assertSame($iterator, $result, 'Wrong iterator retrieved');
    }

    /**
     * Test that `_setIterator()` fails as expected when an invalid value is given.
     *
     * @since [*next-version*]
     */
    public function testSetIteratorFailureInvalid()
    {
        $iterator = uniqid('iterator');
        $exception = $this->createInvalidArgumentException('Invalid iterator');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_createInvalidArgumentException')
            ->with(
                $this->isType('string'),
                null,
                null,
                $iterator
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_setIterator($iterator);
    }
}
