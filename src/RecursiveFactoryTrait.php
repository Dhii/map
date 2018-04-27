<?php

namespace Dhii\Collection;

use ArrayObject;
use Dhii\Factory\FactoryInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface as BaseContainerInterface;
use stdClass;

/**
 * Functionality for recursive map factories.
 *
 * @since [*next-version*]
 */
trait RecursiveFactoryTrait
{
    /**
     * Normalizes a map child element.
     *
     * @since [*next-version*]
     *
     * @param mixed $child The child to normalize.
     *
     * @throws InvalidArgumentException If the child is not valid.
     *
     * @return mixed The normalized element.
     */
    protected function _normalizeChild($child)
    {
        if (is_scalar($child)) {
            return $this->_normalizeScalarChild($child);
        }

        return $this->_normalizeComplexChild($child);
    }

    /**
     * Normalizes a non-scalar child.
     *
     * @param object|array|resource|null $child The child to normalize
     *
     * @throws InvalidArgumentException If the child is not valid.
     *
     * @return mixed
     */
    protected function _normalizeComplexChild($child)
    {
        return $this->_createChildInstance($child);
    }

    /**
     * Creates a new instance of a child element.
     *
     * @since [*next-version*]
     *
     * @param object|array|null $child The child, for which to create a new instance.
     *
     * @return mixed the new child.
     */
    protected function _createChildInstance($child)
    {
        $config  = $this->_getChildConfig($child);
        $factory = $this->_getChildFactory($child);

        return $factory->make($config);
    }

    /**
     * Normalizes a scalar child.
     *
     * @since [*next-version*]
     *
     * @param bool|int|float|string $child The child to normalize.
     *
     * @return mixed The normalized child.
     */
    abstract protected function _normalizeScalarChild($child);

    /**
     * Retrieves the factory that is used to create children instances.
     *
     * @since [*next-version*]
     *
     * @param mixed $child The child for which to get the factory.
     *
     * @return FactoryInterface The child factory.
     */
    abstract protected function _getChildFactory($child);

    /**
     * Retrieves configuration that can be used to make a child instance with a factory.
     *
     * @since [*next-version*]
     *
     * @param mixed $child The child for which to get the config.
     *
     * @return array|stdClass|ArrayObject|BaseContainerInterface The configuration for a child factory.
     */
    abstract protected function _getChildConfig($child);
}