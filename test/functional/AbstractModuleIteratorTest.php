<?php

namespace RebelCode\Modular\FuncTest\Iterator;

use RebelCode\Modular\Iterator\AbstractModuleIterator;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Modular\Iterator\AbstractModuleIterator}.
 *
 * @since [*next-version*]
 */
class AbstractModuleIteratorTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\\Modular\\Iterator\\AbstractModuleIterator';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return AbstractModuleIterator
     */
    public function createInstance()
    {
        $mock = $this->mock(static::TEST_SUBJECT_CLASSNAME)
            ->new();

        return $mock;
    }

    /**
     * Creates a module instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @param string $id
     *
     * @return ModuleInterface
     */
    public function createModule($id)
    {
        $mock = $this->mock('Dhii\\Modular\\ModuleInterface')
            ->getId($id)
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
     * Tests the module list getter and setter methods.
     *
     * @since [*next-version*]
     */
    public function testGetSetModules()
    {
        $subject = $this->createInstance();

        $modules = array(
            'one'   => $this->createModule('one'),
            'mod2'  => $this->createModule('mod2'),
            'third' => $this->createModule('third')
        );

        $subject->this()->_setModules($modules);

        $this->assertEquals(array_values($modules), $subject->this()->_getModules());
    }

    /**
     * Tests the index getter and setter methods.
     *
     * @since [*next-version*]
     */
    public function testGetSetIndex()
    {
        $subject = $this->createInstance();

        $subject->this()->_setIndex(5);

        $this->assertEquals(5, $subject->this()->_getIndex());
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
     * Tests the method that retrieve a module by ID.
     *
     * @since [*next-version*]
     */
    public function testGetModuleById()
    {
        $subject = $this->createInstance();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $subject->this()->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $this->assertSame($module2, $subject->this()->_getModuleById('mod2'));
    }

    /**
     * Tests the method that retrieves a module by ID with a non existing ID.
     *
     * @since [*next-version*]
     */
    public function testGetModuleByIdFail()
    {
        $subject = $this->createInstance();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $subject->this()->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $this->assertNull($subject->this()->_getModuleById('foobar'));
    }

    /**
     * Tests the method that retrieves a module by index.
     */
    public function testGetModuleAtIndex()
    {
        $subject = $this->createInstance();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $subject->this()->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $this->assertSame($module3, $subject->this()->_getModuleAtIndex(2));
    }

    /**
     * Tests the method that retrieves the module at the current index.
     *
     * @since [*next-version*]
     */
    public function testGetModuleAtCurrentIndex()
    {
        $subject = $this->createInstance();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $subject->this()->_setIndex(1);

        $subject->this()->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $this->assertSame($module2, $subject->this()->_getModuleAtCurrentIndex());
    }

    /**
     * Tests the method that retrieves the module at the current index, when it is invalid.
     *
     * @since [*next-version*]
     */
    public function testGetModuleAtCurrentIndexFail()
    {
        $subject = $this->createInstance();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $subject->this()->_setIndex(5);

        $subject->this()->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $this->assertNull($subject->this()->_getModuleAtCurrentIndex());
    }

    /**
     * Tests the method that determines the current module.
     *
     * @since [*next-version*]
     */
    public function testDetermineCurrentModule()
    {
        $subject = $this->createInstance();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $subject->this()->_setIndex(2);

        $subject->this()->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $this->assertSame($module3, $subject->this()->_determineCurrentModule());
    }

    /**
     * Tests the _rewind() method to assert whether the iterate is reset to the first module.
     *
     * @since [*next-version*]
     */
    public function testRewind()
    {
        $subject = $this->createInstance();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $_this = $subject->this();

        $_this->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $_this->_next();
        $_this->_next();
        $_this->_rewind();

        $this->assertEquals(0, $_this->_getIndex());
        $this->assertSame($module1, $_this->_current());
    }

    /**
     * Tests the _current() and _next() method to assert whether the former returns the correct module and
     * whether the latter correctly advances the iterator to the next module.
     *
     * @since [*next-version*]
     */
    public function testCurrentNext()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $_subject->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $_subject->_rewind();
        $this->assertEquals($module1, $_subject->_current());

        $_subject->_next();
        $this->assertEquals($module2, $_subject->_current());

        $_subject->_next();
        $this->assertEquals($module3, $_subject->_current());

        $_subject->_next();
        $this->assertNull($_subject->_current());
    }

    /**
     * Tests the _key() method to assert whether it returns the correct key for the current module.
     *
     * @since [*next-version*]
     */
    public function testKey()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $_subject->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $_subject->_rewind();
        $this->assertEquals($module1->getId(), $_subject->_key());

        $_subject->_next();
        $this->assertEquals($module2->getId(), $_subject->_key());

        $_subject->_next();
        $this->assertEquals($module3->getId(), $_subject->_key());

        $_subject->_next();
        $this->assertNull($_subject->_key());
    }

    /**
     * Tests the _valid() method to assert whether it correctly indicates validity.
     *
     * @since [*next-version*]
     */
    public function testValid()
    {
        $subject  = $this->createInstance();
        $_subject = $subject->this();

        $module1 = $this->createModule('one');
        $module2 = $this->createModule('mod2');
        $module3 = $this->createModule('third');

        $_subject->_setModules(array(
            'one'   => $module1,
            'mod2'  => $module2,
            'third' => $module3
        ));

        $_subject->_rewind();
        $this->assertTrue($_subject->_valid());

        $_subject->_next();
        $this->assertTrue($_subject->_valid());

        $_subject->_next();
        $this->assertTrue($_subject->_valid());

        $_subject->_next();
        $this->assertFalse($_subject->_valid());
    }
}
