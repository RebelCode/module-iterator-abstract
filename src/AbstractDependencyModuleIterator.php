<?php

namespace RebelCode\Modular\Iterator;

use ArrayAccess;
use Dhii\Modular\Module\ModuleInterface;
use Iterator;
use stdClass;
use Traversable;

/**
 * Basic functionality for a module iterator that handles dependencies.
 *
 * @since [*next-version*]
 */
abstract class AbstractDependencyModuleIterator
{
    /**
     * The inner module iterator.
     *
     * @since [*next-version*]
     *
     * @var Iterator
     */
    protected $moduleIterator;

    /**
     * The modules that have already been served, mapped by their keys.
     *
     * @since [*next-version*]
     *
     * @var ModuleInterface[]
     */
    protected $servedModules;

    /**
     * A cache for the module that is currently being served.
     *
     * @since [*next-version*]
     *
     * @var ModuleInterface
     */
    protected $current;

    /**
     * Sets the modules to be iterator over.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface[]|stdClass|Traversable $modules The module instances.
     */
    protected function _setModules($modules)
    {
        $this->moduleIterator = $this->_normalizeIterator($modules);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _rewind()
    {
        $this->servedModules = array();
        $this->current       = null;
        $this->moduleIterator->rewind();

        $this->_next();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _current()
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _key()
    {
        return ($this->current !== null)
            ? $this->current->getKey()
            : null;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _next()
    {
        $previous = $this->current;

        // Mark the previous module as served
        if ($previous !== null) {
            $this->_markModuleAsServed($previous);
        }

        // Keep advancing until an unserved module is found or until end of module list
        while ($this->moduleIterator->valid() && $this->_isModuleServed($this->moduleIterator->current()->getKey())) {
            $this->moduleIterator->next();
        }

        // Get the module from the inner iterator
        $module = $this->moduleIterator->current();

        // Determine _actual_ current module, which may be a dependency of the found unserved module
        $this->current = ($module !== null)
            ? $this->_getDeepMostUnservedModuleDependency($module)
            : null;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _valid()
    {
        return $this->current !== null;
    }

    /**
     * Adds a module to the list of served modules.
     *
     * @param ModuleInterface $module The module instance.
     *
     * @return $this
     */
    protected function _markModuleAsServed(ModuleInterface $module)
    {
        $this->servedModules[$module->getKey()] = $module;

        return $this;
    }

    /**
     * Checks if a module is marked as already served.
     *
     * @since [*next-version*]
     *
     * @param string $key The module key.
     *
     * @return bool True if the module has already been served, false if not.
     */
    protected function _isModuleServed($key)
    {
        return isset($this->servedModules[$key]);
    }

    /**
     * Resolves the actual module to load.
     *
     * Recursively retrieves the module's deep-most unserved dependency.
     *
     * Caters for circular dependency via the $ignore parameter. On every recursive call, the module
     * is recorded in the $ignore list so that it is ignored in subsequent recursive calls.
     *
     * This means that circular dependency in the form of "A requires B, B requires A" will result in
     * B be served prior to A. In other words, the first encountered module will have its dependency
     * loaded before it, even if that dependency requires the module.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface   $module The module instance.
     * @param ModuleInterface[] $ignore The module to ignore.
     *
     * @return ModuleInterface
     */
    protected function _getDeepMostUnservedModuleDependency(ModuleInterface $module, $ignore = array())
    {
        $moduleKey          = $module->getKey();
        $ignore[$moduleKey] = $module;
        $dependencies       = $this->_getUnservedModuleDependencies($module);
        $diffDependencies   = array_diff_key($dependencies, $ignore);

        // If there are no dependencies, return the given module
        if (empty($diffDependencies)) {
            return $module;
        }

        $dependency = array_shift($diffDependencies);

        return $this->_getDeepMostUnservedModuleDependency($dependency, $ignore);
    }

    /**
     * Gets the dependencies of a module that.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface $module The module instance.
     *
     * @return ModuleInterface[] A list of module instances mapped by their keys.
     */
    protected function _getUnservedModuleDependencies(ModuleInterface $module)
    {
        $_this        = $this;
        $dependencies = $this->_getModuleDependencies($module);

        return array_filter($dependencies, function ($dep) use ($_this) {
            return $dep instanceof ModuleInterface && !$_this->_isModuleServed($dep->getKey());
        });
    }

    /**
     * Retrieves the dependencies for a specific module.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface $module The module instance.
     *
     * @return ModuleInterface[]|ArrayAccess
     */
    abstract protected function _getModuleDependencies(ModuleInterface $module);

    /**
     * Normalizes an iterable value into an iterator.
     *
     * If the value is iterable, the resulting iterator would iterate over the
     * elements in the iterable.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable|mixed $iterable The value to normalize.
     *
     * @return Iterator The normalized iterator.
     */
    abstract protected function _normalizeIterator($iterable);
}
