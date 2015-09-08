<?php

namespace Lewis\Presenter;

use BadMethodCallException;

abstract class AbstractPresenter
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
            return true;
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
            return call_user_func_array($this->object, $parameters);
        }

        throw new BadMethodCallException('Method '.$method.' not found on AbstractPresenter.');
    }
}
