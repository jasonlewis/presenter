<?php

namespace Lewis\Presenter;

use ArrayAccess;
use RuntimeException;
use BadMethodCallException;

abstract class AbstractPresenter implements ArrayAccess
{
    /**
     * The object being decorated.
     *
     * @var object
     */
    protected $object;

    /**
     * Create a new presenter instance.
     *
     * @param object $object
     *
     * @return void
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Get an attribute from the wrapped object.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getObjectAttribute($key)
    {
        if (is_array($this->object)) {
            return $this->object[$key];
        }

        return $this->object->{$key};
    }

    /**
     * Dynamically call a method on the presenter or get an attribute from
     * the wrapped object.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (method_exists($this, $key)) {
            return $this->{$key}();
        } elseif (method_exists($this, camel_case($key))) {
            return $this->{camel_case($key)}();
        }

        return $this->getObjectAttribute($key);
    }

    /**
     * Dynamically check if the attribute is set on the object.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        if (method_exists($this, $key) || method_exists($this, camel_case($key))) {
            return ! is_null($this->__get($key));
        } elseif (is_array($this->object)) {
            return isset($this->object[$key]);
        }

        return isset($this->object->{$key});
    }

    /**
     * Dynamically call a method on the wrapped object.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        if (is_object($this->object) && method_exists($this->object, $method)) {
            return call_user_func_array([$this->object, $method], $parameters);
        }

        throw new BadMethodCallException('Method '.$method.' not found on AbstractPresenter.');
    }

    /**
     * Determine if an offset exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get an offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set an offset. Not implemented via the presenter. If required, set directly
     * on the wrapped object.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Cannot dynamically set object properties via presenter.');
    }

    /**
     * Unset an offset. Not implemented via the presenter. If required, unset directly
     * on the wrapped object.
     *
     * @param mixed $offset
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException('Cannot dynamically unset object properties via presenter.');
    }
}
