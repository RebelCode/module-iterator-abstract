<?php

namespace RebelCode\Modular\FuncTest\Iterator;

use RebelCode\Modular\Iterator\AbstractDependencyModuleIterator;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Modular\Iterator\AbstractDependencyModuleIterator}.
 *
 * @since [*next-version*]
 */
class AbstractDependencyModuleIteratorTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\\Modular\\Iterator\\AbstractDependencyModuleIterator';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return AbstractDependencyModuleIterator
     */
    public function createInstance()
    {
        $mock = $this->mock(static::TEST_SUBJECT_CLASSNAME)
            ->_getModuleDependencies(function($module) {
                return $module->getDependencies();
            })
            ->new();

        return $mock;
    }

    /**
     * Creates a module instance for testing.
     *
     * @since [*next-version*]
     *
     * @param string $key  The module key.
     * @param array  $deps An array of module keys.
     *
     * @return ModuleInterface
     */
    public function createModule($key, $deps = array())
    {
        $mock = $this->mock('Dhii\\Modular\\Module\\ModuleInterface')
            ->getKey($key)
            ->getDependencies($deps)
            ->load();

        return $mock->new();
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInstanceOf(
            static::TEST_SUBJECT_CLASSNAME, $subject, 'Subject is not a valid instance.'
        );
    }

    /**
     * Tests the current module getter and setter methods.
     *
     * @since [*next-version*]
     */
    public function testGetSetCurrent()
    {
        $subject = $this->createInstance();

        $module = $this->createModule('foo-bar');
        $subject->this()->_setCurrent($module);

        $this->assertEquals($module, $subject->this()->_getCurrent());
    }

    /**
     * Tests the served modules list getter and setter methods.
     *
     * @since [*next-version*]
     */
    public function testGetSetServedModules()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $modules = array(
            'one' => $this->createModule('one'),
            'two' => $this->createModule('two')
        );

        $_subject->_setServedModules($modules);

        $this->assertEquals($modules, $_subject->_getServedModules());
    }

    /**
     * Tests the method that adds a module to the served modules list.
     *
     * @since [*next-version*]
     */
    public function testAddServedModules()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $modules = array(
            'one' => $this->createModule('one'),
            'two' => $this->createModule('two')
        );
        $module3 = $this->createModule('three');

        $_subject->_setServedModules($modules);
        $_subject->_addServedModule($module3);

        $expected = array_merge($modules, array('three' => $module3));

        $this->assertEquals($expected, $_subject->_getServedModules());
    }

    /**
     * Tests the method that removes a module from the served modules list.
     *
     * @since [*next-version*]
     */
    public function testRemoveServedModules()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $modules = array(
            'one' => $this->createModule('one'),
            'two' => $module2 = $this->createModule('two')
        );

        $_subject->_setServedModules($modules);
        $_subject->_removeServedModule('one');

        $expected = array('two' => $module2);

        $this->assertEquals($expected, $_subject->_getServedModules());
    }

    /**
     * Tests the method that checks if a module is marked as served.
     *
     * @since [*next-version*]
     */
    public function testIsModuleServed()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $modules = array(
            'one' => $this->createModule('one'),
            'two' => $this->createModule('two')
        );

        $_subject->_setServedModules($modules);

        $this->assertTrue($_subject->_isModuleServed('one'));
        $this->assertTrue($_subject->_isModuleServed('two'));
        $this->assertFalse($_subject->_isModuleServed('three'));
    }

    /**
     * Tests the method that retrieves the unserved dependencies of a module.
     *
     * @since [*next-version*]
     */
    public function testGetUnservedModuleDependencies()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $module = $this->createModule('test', array(
            'dep1' => $dep1 = $this->createModule('dep1'),
            'dep2' => $dep2 = $this->createModule('dep2'),
            'dep3' => $dep3 = $this->createModule('dep3'),
            'dep4' => $dep4 = $this->createModule('dep4'),
        ));

        $_subject->_setServedModules(array(
            'dep1' => $dep1,
            'dep3' => $dep3
        ));

        $expected = array(
            'dep2' => $dep2,
            'dep4' => $dep4
        );

        $this->assertEquals($expected, $_subject->_getUnservedModuleDependencies($module));
    }

    /**
     * Tests the method that gets the deep-most unserved dependency of a module.
     *
     * @since [*next-version*]
     */
    public function testGetDeepMostUnservedModuleDependency()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $module = $this->createModule('test', array(
            'dep1' => $dep1 = $this->createModule('dep1'),
            'dep2' => $dep2 = $this->createModule('dep2', array(
                'dep2a' => $dep2a = $this->createModule('dep2a'),
                'dep2b' => $dep2b = $this->createModule('dep2b'),
            )),
            'dep3' => $dep3 = $this->createModule('dep3')
        ));

        $_subject->_setServedModules(array(
            'dep1'  => $dep1,
            'dep2a' => $dep2a
        ));

        $this->assertEquals($dep2b, $_subject->_getDeepMostUnservedModuleDependency($module));
    }

    /**
     * Tests the method that gets the deep-most unserved dependency of a module with no dependencies.
     *
     * @since [*next-version*]
     */
    public function testGetDeepMostUnservedModuleDependencyNoDeps()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $module = $this->createModule('test');

        $this->assertEquals($module, $_subject->_getDeepMostUnservedModuleDependency($module));
    }

    /**
     * Tests the method that gets the deep-most unserved dependency of a module with all of its dependencies served.
     *
     * @since [*next-version*]
     */
    public function testGetDeepMostUnservedModuleDependencyAllServed()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $module = $this->createModule('test', array(
            'dep1' => $dep1 = $this->createModule('dep1'),
            'dep2' => $dep2 = $this->createModule('dep2')
        ));

        $_subject->_setServedModules(array(
            'dep1' => $dep1,
            'dep2' => $dep2,
        ));

        $this->assertEquals($module, $_subject->_getDeepMostUnservedModuleDependency($module));
    }

    /**
     * Tests the method that gets the deep-most unserved dependency of a module with a circular dependency scenario.
     *
     * @since [*next-version*]
     */
    public function testGetDeepMostUnservedModuleDependencyCircular()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $module = $this->createModule('test');
        $dep    = $this->createModule('dep', array($module));

        $module->mock()->getDependencies(function() use ($dep) {
            return array('dep' => $dep);
        });

        $this->assertEquals($dep, $_subject->_getDeepMostUnservedModuleDependency($module));
    }

    /**
     * Tests the method that determines the current module to serve.
     *
     * @since [*next-version*]
     */
    public function testDetermineCurrentModule()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $_subject->_setModules(array(
            'one'   => $modOne = $this->createModule('one'),
            'two'   => $modTwo = $this->createModule('two', array(
                'two-dep' => $modTwoDep = $this->createModule('two-dep')
            )),
            'three' => $modThree = $this->createModule('three')
        ));

        $_subject->_setIndex(0);

        $this->assertSame($modOne, $_subject->_determineCurrentModule());
    }

    /**
     * Tests the method that determines the current module to serve when it has no dependencies.
     *
     * @since [*next-version*]
     */
    public function testDetermineCurrentModuleWithDeps()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $_subject->_setModules(array(
            'one'  => $modOne = $this->createModule('one'),
            'two'  => $modTwo = $this->createModule('two', array(
                'two-dep' => $modTwoDep = $this->createModule('two-dep')
            )),
            'three' => $modThree = $this->createModule('three')
        ));

        $_subject->_setIndex(1);

        $this->assertSame($modTwoDep, $_subject->_determineCurrentModule());
    }

    /**
     * Tests the method that determines the current module to serve when all dependencies have been served.
     *
     * @since [*next-version*]
     */
    public function testDetermineCurrentModuleWithDepsServed()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $_subject->_setModules(array(
            'one'   => $modOne = $this->createModule('one'),
            'two'   => $modTwo = $this->createModule('two', array(
                'two-dep' => $modTwoDep = $this->createModule('two-dep')
            )),
            'three' => $modThree = $this->createModule('three')
        ));

        $_subject->_setIndex(1);
        $_subject->_addServedModule($modTwoDep);

        $this->assertSame($modTwo, $_subject->_determineCurrentModule());
    }

    /**
     * Tests the rewind method to determine if the served modules list is reset.
     *
     * @since [*next-version*]
     */
    public function testRewind()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $_subject->_setModules(array(
            'one'   => $modOne = $this->createModule('one'),
            'two'   => $modTwo = $this->createModule('two', array(
                'two-dep' => $modTwoDep = $this->createModule('two-dep')
            )),
            'three' => $modThree = $this->createModule('three')
        ));

        $_subject->_setIndex(1);
        $_subject->_addServedModule($modOne);
        $_subject->_addServedModule($modTwoDep);
        $_subject->_addServedModule($modTwo);

        $_subject->_rewind();

        $this->assertEmpty($_subject->_getServedModules());
    }

    /**
     * Tests the next method to assert whether the iterator correctly advances to the next module.
     *
     * @since [*next-version*]
     */
    public function testNext()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $_subject->_setModules(array(
            'one'   => $modOne = $this->createModule('one'),
            'two'   => $modTwo = $this->createModule('two'),
            'three' => $modThree = $this->createModule('three')
        ));

        $_subject->_rewind();

        $this->assertSame($modOne, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modTwo, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modThree, $_subject->_current());

        $_subject->_next();
        $this->assertNull($_subject->_current());
    }

    /**
     * Tests the next method with a dependency to assert whether the dependency is served first.
     *
     * @since [*next-version*]
     */
    public function testNextWithDependency()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $modThree = $this->createModule('three');

        $_subject->_setModules(array(
            'one'   => $modOne = $this->createModule('one'),
            'two'   => $modTwo = $this->createModule('two', array(
                'three' => $modThree
            )),
            'three' => $modThree
        ));

        $_subject->_rewind();

        $this->assertSame($modOne, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modThree, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modTwo, $_subject->_current());

        $_subject->_next();
        $this->assertNull($_subject->_current());
    }

    /**
     * Tests the next method with a deep dependency scenario to assert whether the order to modules
     * served is correct.
     *
     * @since [*next-version*]
     */
    public function testNextWithDeepDependency()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $modFour  = $this->createModule('four');
        $modThree = $this->createModule('three', array(
            'four' => $modFour
        ));

        $_subject->_setModules(array(
            'one'   => $modOne = $this->createModule('one'),
            'two'   => $modTwo = $this->createModule('two', array(
                'three' => $modThree
            )),
            'three' => $modThree,
            'four'  => $modFour
        ));

        $_subject->_rewind();

        $this->assertSame($modOne, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modFour, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modThree, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modTwo, $_subject->_current());

        $_subject->_next();
        $this->assertNull($_subject->_current());
    }

    /**
     * Tests the next method with a module that has multiple dependencies to assert whether all the
     * dependencies are served before the module.
     *
     * @since [*next-version*]
     */
    public function testNextWithMultipleDependencies()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $modThree = $this->createModule('three');
        $modFour  = $this->createModule('four');

        $_subject->_setModules(array(
            'one'   => $modOne = $this->createModule('one'),
            'two'   => $modTwo = $this->createModule('two', array(
                'three' => $modThree,
                'four'  => $modFour
            )),
            'three' => $modThree,
            'four'  => $modFour
        ));

        $_subject->_rewind();
        $_subject->_current();

        $this->assertSame($modOne, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modThree, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modFour, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modTwo, $_subject->_current());

        $_subject->_next();
        $this->assertNull($_subject->_current());
    }

    /**
     * Tests the next method with a scenario where a module has dependencies that have already been served
     * to assert whether or not they are served twice.
     *
     * @since [*next-version*]
     */
    public function testNextWithServedDependencies()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $modOne = $this->createModule('one');
        $modTwo = $this->createModule('two');

        $_subject->_setModules(array(
            'one'   => $modOne,
            'two'   => $modTwo,
            'three' => $modThree = $this->createModule('three', array(
                'one' => $modOne,
                'two' => $modTwo,
            )),
            'four'  => $modFour = $this->createModule('four')
        ));

        $_subject->_rewind();
        $_subject->_current();

        $this->assertSame($modOne, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modTwo, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modThree, $_subject->_current());

        $_subject->_next();
        $this->assertSame($modFour, $_subject->_current());

        $_subject->_next();
        $this->assertNull($_subject->_current());
    }
}
