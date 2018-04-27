<?php

namespace Dhii\Collection\UnitTest;

use Dhii\Collection\MapFactoryInterface;
use Dhii\Collection\MapInterface;
use Dhii\Collection\RecursiveFactoryTrait as TestSubject;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class RecursiveFactoryTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Collection\RecursiveFactoryTrait';

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
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
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

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a mock that uses traits.
     *
     * This is particularly useful for testing integration between multiple traits.
     *
     * @since [*next-version*]
     *
     * @param string[] $traitNames Names of the traits for the mock to use.
     *
     * @return MockBuilder The builder for a mock of an object that uses the traits.
     */
    public function mockTraits($traitNames = [])
    {
        $paddingClassName = uniqid('Traits');
        $definition = vsprintf('abstract class %1$s {%2$s}', [
            $paddingClassName,
            implode(
                ' ',
                array_map(
                    function ($v) {
                        return vsprintf('use %1$s;', [$v]);
                    },
                    $traitNames)),
        ]);
        var_dump($definition);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
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
     * Creates a new Map instance.
     *
     * @since [*next-version*]
     *
     * @param array|null $methods The methods to mock, if any.
     *
     * @return MockObject|MapInterface The new map.
     */
    public function createMap($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $mock = $this->getMockBuilder('Dhii\Collection\MapInterface')
            ->setMethods($methods)
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Map Factory instance.
     *
     * @since [*next-version*]
     *
     * @param array|null $methods The methods to mock, if any.
     *
     * @return MockObject|MapFactoryInterface The new map factory.
     */
    public function createMapFactory($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $mock = $this->getMockBuilder('Dhii\Collection\MapFactoryInterface')
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
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests whether `_normalizeChild()` works as expected when normalizing a scalar type.
     *
     * @since [*next-version*]
     */
    public function testNormalizeChildScalar()
    {
        $child = uniqid('child');
        $normalized = uniqid('normalized');
        $subject = $this->createInstance(['_normalizeScalarChild']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeScalarChild')
            ->with($child)
            ->will($this->returnValue($normalized));

        $result = $_subject->_normalizeChild($child);
        $this->assertEquals($normalized, $result, 'Wrong scalar normalization result');
    }

    /**
     * Tests whether `_normalizeChild()` works as expected when normalizing a complex type.
     *
     * @since [*next-version*]
     */
    public function testNormalizeChildComplex()
    {
        $child = (object) [uniqid('key') => uniqid('val')];
        $normalized = uniqid('normalized');
        $subject = $this->createInstance(['_normalizeComplexChild']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeComplexChild')
            ->with($child)
            ->will($this->returnValue($normalized));

        $result = $_subject->_normalizeChild($child);
        $this->assertEquals($normalized, $result, 'Wrong scalar normalization result');
    }

    /**
     * Tests whether `_createChildInstance()` works as expected.
     *
     * @since [*next-version*]
     */
    public function testCreateChildInstance()
    {
        $child = uniqid('child');
        $config = [uniqid('key') => uniqid('val')];
        $childConfig = [uniqid('key') => uniqid('val')];
        $childFactory = $this->createMapFactory();
        $map = $this->createMap();
        $subject = $this->createInstance(['_getChildConfig', '_getChildFactory']);
        $_subject = $this->reflect($subject);

        $childFactory->expects($this->exactly(1))
            ->method('make')
            ->with($childConfig)
            ->will($this->returnValue($map));

        $subject->expects($this->exactly(1))
            ->method('_getChildConfig')
            ->with($child, $config)
            ->will($this->returnValue($childConfig));
        $subject->expects($this->exactly(1))
            ->method('_getChildFactory')
            ->with($child, $config)
            ->will($this->returnValue($childFactory));

        $result = $_subject->_createChildInstance($child, $config);
        $this->assertSame($map, $result, 'Wrong child instance returned');
    }
}
