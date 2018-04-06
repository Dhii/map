<?php

namespace Dhii\Collection\UnitTest;

use ArrayIterator;
use ArrayObject;
use InvalidArgumentException;
use Xpmock\TestCase;
use Dhii\Collection\CountableMap as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Exception as RootException;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class CountableMapTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Collection\CountableMap';

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
     * Creates a new Not Found exception.
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
     * Creates a new traversable.
     *
     * @since [*next-version*]
     *
     * @param array      $data    The data for the store.
     * @param array|null $methods Names of methods to mock.
     *
     * @return ArrayIterator|MockObject The new traversable mock.
     */
    public function createTraversable($data = [], $methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $builder = $this->getMockBuilder('ArrayIterator')
            ->setConstructorArgs([$data]);
        $builder->setMethods($methods);
        $mock = $builder->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @see https://bugs.php.net/bug.php?id=61943
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance([], [], true);

        $this->assertInstanceOf(
            static::TEST_SUBJECT_CLASSNAME,
            $subject,
            'A valid instance of the test subject could not be created.'
        );
        $this->assertInstanceOf(
            'Countable',
            $subject,
            'Subject does not implement required interface'
        );
        $this->assertInstanceOf(
            'Traversable',
            $subject,
            'Subject does not implement required interface'
        );
        $this->assertInstanceOf(
            'Dhii\Data\Container\HasCapableInterface',
            $subject,
            'Subject does not implement required interface'
        );
        $this->assertInstanceOf(
            'Dhii\Data\Container\ContainerInterface',
            $subject,
            'Subject does not implement required interface'
        );
        $this->assertInstanceOf(
            'Dhii\Collection\CountableListInterface',
            $subject,
            'Subject does not implement required interface'
        );
    }

    /**
     * Tests that the constructor is working as expected when given a data store.
     *
     * @since [*next-version*]
     */
    public function testConstructorStore()
    {
        $elements = [uniqid('key') => uniqid('val')];
        $store = $this->createStore($elements);
        $subject = $this->createInstance(['_setDataStore'], [], true);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_setDataStore')
            ->with($store);

        $subject->__construct($store);
    }

    /**
     * Tests that the constructor is working as expected when given an object.
     *
     * @since [*next-version*]
     */
    public function testConstructorObject()
    {
        $elements = [uniqid('key') => uniqid('val')];
        $object = (object) $elements;
        $store = $this->createStore($object);
        $subject = $this->createInstance(['_setDataStore', '_createDataStore'], [], true);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_createDataStore')
            ->with($object)
            ->will($this->returnValue($store));

        $subject->expects($this->exactly(1))
            ->method('_setDataStore')
            ->with($store);

        $subject->__construct($object);
    }

    /**
     * Tests that the constructor is working as expected when given an array.
     *
     * @since [*next-version*]
     */
    public function testConstructorArray()
    {
        $elements = [uniqid('key') => uniqid('val')];
        $store = $this->createStore($elements);
        $subject = $this->createInstance(['_setDataStore', '_createDataStore'], [], true);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_createDataStore')
            ->with($elements)
            ->will($this->returnValue($store));

        $subject->expects($this->exactly(1))
            ->method('_setDataStore')
            ->with($store);

        $subject->__construct($elements);
    }

    /**
     * Tests that the constructor fails as expected when given invalid data.
     *
     * @since [*next-version*]
     */
    public function testConstructorFailureInvalidData()
    {
        $elements = uniqid('data');
        $exception = $this->createInvalidArgumentException('Invalid data');
        $subject = $this->createInstance(['_createInvalidArgumentException'], [], true);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_createInvalidArgumentException')
            ->with(
                $this->isType('string'),
                null,
                null,
                $elements
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('InvalidArgumentException');
        $subject->__construct($elements);
    }
}
