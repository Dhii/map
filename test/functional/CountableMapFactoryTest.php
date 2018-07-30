<?php

namespace Dhii\Collection\FuncTest;

use ArrayIterator;
use ArrayObject;
use stdClass;
use Traversable;
use Xpmock\TestCase;
use Dhii\Collection\CountableMapFactory as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Exception as RootException;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class CountableMapFactoryTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Collection\CountableMapFactory';

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
            ->will($this->returnCallback(function ($string, $values) {
                return vsprintf($string, $values);
            }));

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
     * Tests that the factory will create elements of correct types up to 2 levels deep.
     *
     * This should prove correct behaviour at any depth, because it is recursive.
     *
     * @since [*next-version*]
     */
    public function testMakeTypes2Level()
    {
        $key1 = uniqid('key');
        $data = [
            $key1 => [
                uniqid('key') => uniqid('val'),
                uniqid('key') => uniqid('val'),
                uniqid('key') => uniqid('val'),
            ],
        ];
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $subject->make([TestSubject::K_DATA => $data]);
        $this->assertInstanceOf(TestSubject::PRODUCT_CLASS_NAME, $result, 'Wrong type of result at lvl 1');

        $level2 = $result->get($key1);
        $this->assertInstanceOf(TestSubject::PRODUCT_CLASS_NAME, $level2, 'Wrong type of result at lvl 2');
    }

    /**
     * Tests that the structure of the hierarchy created by the factory is correct.
     *
     * This tests 3 levels deep.
     *
     * @since [*next-version*]
     */
    public function testMakeStructure()
    {
        $data = [
            uniqid('key') => [
                uniqid('key') => uniqid('val'),
                uniqid('key') => uniqid('val'),
                uniqid('key') => uniqid('val'),
            ],
            uniqid('key') => [
                uniqid('key') => uniqid('val'),
                uniqid('key') => uniqid('val'),
                uniqid('key') => [
                    uniqid('key') => uniqid('val'),
                    uniqid('key') => uniqid('val'),
                    uniqid('key') => uniqid('val'),
                ],
            ],
        ];

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $subject->make([TestSubject::K_DATA => $data]);
        $this->assertEquals($data, $this->_iterableToArrayRecursive($result), 'Wrong structure of result');
    }

    /**
     * Converts a hierarchy of iterables to an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $iterable The iterable to convert.
     *
     * @return array The array with the same structure as the iterable hierarchy.
     */
    protected function _iterableToArrayRecursive($iterable)
    {
        $result = [];
        foreach ($iterable as $_key => $_value) {
            $value = (is_array($_value)
                || ($_value instanceof Traversable)
                || ($_value instanceof stdClass))
                ? $this->_iterableToArrayRecursive($_value)
                : $_value;
            $result[$_key] = $value;
        }

        return $result;
    }
}
