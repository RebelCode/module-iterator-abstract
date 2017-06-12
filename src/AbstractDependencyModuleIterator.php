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
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _next()
    {
        $current = $this->_getCurrent();

        // Mark the current module as served
        if (!is_null($current)) {
            $this->_addServedModule($current);
        }

        // Keep advancing until an unserved module is found or until end of module list
        while ($this->_valid() && $this->_isModuleServed(parent::_determineCurrentModule()->getKey())) {
            parent::_next();
        }
    }
}
