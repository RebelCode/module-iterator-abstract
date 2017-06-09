<?php

namespace RebelCode\Modular\Iterator;

use Dhii\Modular\ModuleInterface;

/**
 * Basic functionality for a module iterator.
 *
 * @since [*next-version*]
 */
abstract class AbstractModuleIterator
{
    /**
     * The modules to be iterated.
     *
     * @since [*next-version*]
     *
     * @var ModuleInterface[]
     */
    protected $modules;

    /**
     * A map of the module instances using the module IDs as keys.
     *
     * @since [*next-version*]
     *
     * @var ModuleInterface[]
     */
    protected $moduleMap;

    /**
     * The numeric index of the current module being served.
     *
     * @since [*next-version*]
     *
     * @var int
     */
    protected $index;

    /**
     * A cache for the module that is currently being served.
     *
     * @since [*next-version*]
     *
     * @var ModuleInterface
     */
    protected $current;

    /**
     * Retrieves the modules to be iterated.
     *
     * @since [*next-version*]
     *
     * @return ModuleInterface[] An array of modules.
     */
    protected function _getModules()
    {
        return $this->modules;
    }

    /**
     * Sets the modules to be served.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface[] $modules The module instances
     *
     * @return $this
     */
    protected function _setModules(array $modules)
    {
        $this->modules   = array_values($modules);
        $this->moduleMap = $this->_createModuleMap($this->modules);

        return $this;
    }

    /**
     * Creates a map of modules, mapped by their ID, from a given module list.
     *
     * @since [*next-version*]
     *
     * @param array $modules The list of modules.
     *
     * @return array The modules, mapped by their IDs.
     */
    protected function _createModuleMap(array $modules)
    {
        $map = array();

        foreach ($modules as $_module) {
            $map[$_module->getId()] = $_module;
        }

        return $map;
    }

    /**
     * Retrieves the module with a specific ID.
     *
     * @since [*next-version*]
     *
     * @param string $moduleId The module ID.
     *
     * @return ModuleInterface|null The module with the given ID or null if the module ID was not found.
     */
    protected function _getModuleById($moduleId)
    {
        return isset($this->moduleMap[$moduleId])
            ? $this->moduleMap[$moduleId]
            : null;
    }

    /**
     * Gets the numeric index of the current module being served.
     *
     * @since [*next-version*]
     *
     * @return int
     */
    protected function _getIndex()
    {
        return $this->index;
    }

    /**
     * Sets the numeric index of the current module to serve.
     *
     * @since [*next-version*]
     *
     * @param int $index A zero-based index integer.
     *
     * @return $this
     */
    protected function _setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Retrieves the module at a specific numeric index.
     *
     * @since [*next-version*]
     *
     * @param int $index The numeric, zero-based index.
     *
     * @return ModuleInterface|null The module instance or null if the index is invalid.
     */
    protected function _getModuleAtIndex($index)
    {
        $modules = $this->_getModules();

        return isset($modules[$index])
            ? $modules[$index]
            : null;
    }

    /**
     * Retrieves the module at the current index.
     *
     * @since [*next-version*]
     *
     * @return ModuleInterface|null The module instance or null if the current index is invalid.
     */
    protected function _getModuleAtCurrentIndex()
    {
        return $this->_getModuleAtIndex($this->_getIndex());
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
     * Determines which module should currently be served.
     *
     * @since [*next-version*]
     *
     * @return ModuleInterface|null The module instance or null on failure to determine the module.
     */
    protected function _determineCurrentModule()
    {
        return $this->_getModuleAtCurrentIndex();
    }

    /**
     * Rewinds the iterator to the first element.
     *
     * @since [*next-version*]
     */
    protected function _rewind()
    {
        $this->_setIndex(0)
            ->_setCurrent(null);
    }

    /**
     * Serves the current element.
     *
     * @since [*next-version*]
     *
     * @return ModuleInterface
     */
    protected function _current()
    {
        $this->_setCurrent($this->_determineCurrentModule());

        return $this->_getCurrent();
    }

    /**
     * Gets the key of the current element.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _key()
    {
        $current = $this->_current();

        return is_null($current)
            ? null
            : $current->getId();
    }

    /**
     * Moves the iterator to the next element.
     *
     * @since [*next-version*]
     */
    protected function _next()
    {
        $this->_setIndex($this->_getIndex() + 1);
    }

    /**
     * Checks if the current element is valid.
     *
     * @since [*next-version*]
     *
     * @return bool True if the element is valid, false if not.
     */
    protected function _valid()
    {
        return $this->_getModuleAtCurrentIndex() !== null;
    }
}
