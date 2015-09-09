<?php

namespace Lewis\Presenter;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pagination\AbstractPaginator as Paginator;

class Decorator
{
    /**
     * Array of registered decorator bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Application container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Create a new decorate instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a class and presenter with the decorator.
     *
     * @param string $class
     * @param string $presenter
     *
     * @return void
     */
    public function register($class, $presenter)
    {
        $this->bindings[$class] = $presenter;
    }

    /**
     * Set the bindings on the decorator.
     *
     * @param array $bindings
     *
     * @return void
     */
    public function setBindings(array $bindings)
    {
        $this->bindings = $bindings;
    }

    /**
     * Attempt to decorate an object.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    public function decorate($object)
    {
        if (is_array($object) || $object instanceof Collection || $object instanceof Paginator) {
            return $this->decorateArray($object);
        }

        if (! is_object($object)) {
            return $object;
        }

        $object = clone $object;

        if ($object instanceof Model) {
            $this->decorateModelRelations($object);
        }

        if ($this->hasBinding($object)) {
            $binding = $this->getBinding($object);

            return $this->container->make($binding, compact('object'));
        }

        return $object;
    }

    /**
     * Decorate an Eloquent models relations.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected function decorateModelRelations(Model $model)
    {
        if ($relations = $model->getRelations()) {
            foreach ($relations as $key => $value) {
                $model->setRelation($key, $this->decorate($value));
            }
        }
    }

    /**
     * Decorate an array of objects.
     *
     * @param mixed $array
     *
     * @return mixed
     */
    protected function decorateArray($array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->decorate($value);
        }

        return $array;
    }

    /**
     * Determine if an object has a presenter binding.
     *
     * @param object $object
     *
     * @return bool
     */
    protected function hasBinding($object)
    {
        return isset($this->bindings[get_class($object)]);
    }

    /**
     * Get an objects presenter binding.
     *
     * @param object $object
     *
     * @return \Lewis\Presenter\AbstractPresenter
     */
    protected function getBinding($object)
    {
        return $this->bindings[get_class($object)];
    }
}
