<?php

namespace Dhii\Collection\FuncTest;

use ArrayIterator;
use ArrayObject;
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
    }

    /**
     * Tests whether countable functionality works as expected.
     *
     * @since [*next-version*]
     */
    public function testArrayCountable()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $data = [
            $key1 => $val1,
            $key2 => $val2,
        ];
        $subject = $this->createInstance(null, [$data]);
        $_subject = $this->reflect($subject);

        $result1 = count($subject);
        $this->assertEquals(count($data), $result1, 'Count of subject elements was not determined correctly');

        $result2 = iterator_to_array($subject);
        $this->assertEquals($data, $result2, 'Iterating over the subject did not produce expected results');

        $this->assertTrue($subject->has($key1), 'Subject did not detect having the first key');
        $this->assertTrue($subject->has($key2), 'Subject did not detect having the second key');

        $this->assertEquals($subject->get($key1), $val1, 'Subject returned wrong value for the first key');
        $this->assertEquals($subject->get($key2), $val2, 'Subject returned wrong value for the second key');

        $this->assertFalse($subject->has(uniqid('random-key')), 'Subject wrongly detected having a non-existing key');

        $this->setExpectedException('Dhii\Data\Container\Exception\NotFoundException');
        $this->assertFalse($subject->get(uniqid('random-key')), 'Subject retrieved a non-existing key');
    }
}
