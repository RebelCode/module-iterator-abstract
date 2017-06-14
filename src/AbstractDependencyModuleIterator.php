<?php

namespace RebelCode\Modular\Iterator;

use ArrayAccess;
use Dhii\Modular\Module\ModuleInterface;

/**
 * Description of AbstractDependencyModuleIterator.
 *
 * @since [*next-version*]
 */
abstract class AbstractDependencyModuleIterator extends AbstractModuleIterator
{
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
     * Retrieves the list of modules that have already been served.
     *
     * @since [*next-version*]
     *
     * @return ModuleInterface[] An array of module instances mapped by their keys.
     */
    protected function _getServedModules()
    {
        return $this->servedModules;
    }

    /**
     * Sets the modules that have already been served.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface[] $served An array of module instances mapped by their keys.
     *
     * @return $this
     */
    protected function _setServedModules(array $served)
    {
        $this->servedModules = $served;

        return $this;
    }

    /**
     * Adds a module to the list of served modules.
     *
     * @param ModuleInterface $module The module instance.
     *
     * @return $this
     */
    protected function _addServedModule(ModuleInterface $module)
    {
        $this->servedModules[$module->getKey()] = $module;

        return $this;
    }

    /**
     * Removes a module from the list of served modules.
     *
     * @since [*next-version*]
     *
     * @param string $key The module key.
     *
     * @return $this
     */
    protected function _removeServedModule($key)
    {
        unset($this->servedModules[$key]);

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
     * Gets the current module being served.
     *
     * This method should be an inexpensive call to a cached result.
     *
     * @see AbstractModuleIterator::_determineCurrentModule()
     * @since [*next-version*]
     *
     * @return ModuleInterface|null The module instance or null if no module is being served.
     */
    protected function _getCurrent()
    {
        return $this->current;
    }

    /**
     * Sets the current module to serve.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface|null $current The module instance to serve. Default: null
     *
     * @return $this
     */
    protected function _setCurrent(ModuleInterface $current = null)
    {
        $this->current = $current;

        return $this;
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
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return ModuleInterface
     */
    protected function _determineCurrentModule()
    {
        $module = parent::_determineCurrentModule();

        return is_null($module)
            ? null
            : $this->_getDeepMostUnservedModuleDependency($module);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _rewind()
    {
        parent::_rewind();

        $this->_setServedModules(array());
        $this->_next();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _current()
    {
        return $this->_getCurrent();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _next()
    {
        // Mark the previous module as served
        if (!is_null($previous = $this->_getCurrent())) {
            $this->_addServedModule($previous);
        }

        // Keep advancing until an unserved module is found or until end of module list
        while ($this->_valid() && $this->_isModuleServed(parent::_determineCurrentModule()->getKey())) {
            parent::_next();
        }

        // Determine _actual_ current module, which may be a depedency of the found unserved module
        $this->_setCurrent($this->_determineCurrentModule());
    }
}
