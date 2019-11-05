<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Test\Chippyash\StateMachine\Builder;

use Chippyash\StateMachine\Builder\XmlBuilder;
use Chippyash\StateMachine\Exceptions\InvalidStateMachineFileException;
use Chippyash\StateMachine\StateGraph;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class XmlBuilderTest extends TestCase
{
    /**
     * @var XmlBuilder
     */
    protected $sut;

    protected function setUp()
    {
        $this->sut = new XmlBuilder();
    }

    public function testYouCanBuildAStategraphFromXml()
    {
        $test = $this->sut->build(dirname(__DIR__, 3) . '/docs/stategraph-example.xml');
        $this->assertInstanceOf(StateGraph::class, $test);
        $states = $test->getStates();
        $this->assertEquals(['start','build','compile','done'], array_keys($states->toArray()));
        $transitions = $test->getTransitions();
        $this->assertEquals(['building','built','compiled','failedbuild'], array_keys($transitions->toArray()));
    }

    public function testExceptionIsThrownIfXmlFileDoesNotExist()
    {
        $this->expectException(InvalidStateMachineFileException::class);
        $this->sut->build('foobar.xml');
    }

    public function testYouCanOptionallyValidateTheXmlInput()
    {
        $test = $this->sut->build(
            dirname(__DIR__, 3) . '/docs/stategraph-example.xml',
            true
        );
        $this->assertInstanceOf(StateGraph::class, $test);
    }

    public function testValidatingInvalidXmlWillThrowAnExceptionForMissingGraphName()
    {
        //no name for graph
        $xml1 = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<graph description="test xml stategraph">
    <states>
        <state name="start" description="Starting State"></state>
        <state name="build" description="Build state"></state>
        <state name="compile" description=""></state>
        <state name="done" description="Finished"></state>
    </states>
    <transitions>
        <transition name="building" description="beginning build" from="start" to="build"></transition>
        <transition name="built" description="artifact is built" from="build" to="compile"></transition>
        <transition name="compiled" description="artifact compiled" from="compile" to="done"></transition>
        <transition name="failedbuild" description="failed the build" from="build" to="start"></transition>
    </transitions>
</graph>
XML;
        $dir = vfsStream::create([
            'bad1.xml' => $xml1
        ],
            vfsStream::setup('root')
        );
        $this->expectException(InvalidStateMachineFileException::class);
        $this->expectExceptionMessage("XML is not valid: Error 1868: Element 'graph': The attribute 'name' is required but missing.");
        $this->sut->build(
            $dir->url() . '/bad1.xml',
            true
        );
    }

    public function testValidatingInvalidXmlWillThrowAnExceptionForMissingStateName()
    {
        //no name for state
        $xml1 = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<graph name="test" description="test xml stategraph">
    <states>
        <state description="Starting State"></state>
        <state name="build" description="Build state"></state>
        <state name="compile" description=""></state>
        <state name="done" description="Finished"></state>
    </states>
    <transitions>
        <transition name="building" description="beginning build" from="start" to="build"></transition>
        <transition name="built" description="artifact is built" from="build" to="compile"></transition>
        <transition name="compiled" description="artifact compiled" from="compile" to="done"></transition>
        <transition name="failedbuild" description="failed the build" from="build" to="start"></transition>
    </transitions>
</graph>
XML;
        $dir = vfsStream::create([
            'bad1.xml' => $xml1
        ],
            vfsStream::setup('root')
        );
        $this->expectException(InvalidStateMachineFileException::class);
        $this->expectExceptionMessage("XML is not valid: Error 1868: Element 'state': The attribute 'name' is required but missing.");
        $this->sut->build(
            $dir->url() . '/bad1.xml',
            true
        );
    }

    public function testValidatingInvalidXmlWillThrowAnExceptionForMissingTransitionName()
    {
        //no name for transition
        $xml1 = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<graph name="test" description="test xml stategraph">
    <states>
        <state name="start" description="Starting State"></state>
        <state name="build" description="Build state"></state>
        <state name="compile" description=""></state>
        <state name="done" description="Finished"></state>
    </states>
    <transitions>
        <transition description="beginning build" from="start" to="build"></transition>
        <transition name="built" description="artifact is built" from="build" to="compile"></transition>
        <transition name="compiled" description="artifact compiled" from="compile" to="done"></transition>
        <transition name="failedbuild" description="failed the build" from="build" to="start"></transition>
    </transitions>
</graph>
XML;
        $dir = vfsStream::create([
            'bad1.xml' => $xml1
        ],
            vfsStream::setup('root')
        );
        $this->expectException(InvalidStateMachineFileException::class);
        $this->expectExceptionMessage("XML is not valid: Error 1868: Element 'transition': The attribute 'name' is required but missing.");
        $this->sut->build(
            $dir->url() . '/bad1.xml',
            true
        );
    }

    public function testValidatingInvalidXmlWillThrowAnExceptionForNumberOfStatesLessThanTwo()
    {
        // count(states) < 2
        $xml1 = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<graph name="test" description="test xml stategraph">
    <states>
        <state name="start" description="Starting State"></state>
    </states>
    <transitions>
        <transition name="building" description="beginning build" from="start" to="build"></transition>
        <transition name="built" description="artifact is built" from="build" to="compile"></transition>
        <transition name="compiled" description="artifact compiled" from="compile" to="done"></transition>
        <transition name="failedbuild" description="failed the build" from="build" to="start"></transition>
    </transitions>
</graph>
XML;
        $dir = vfsStream::create([
            'bad1.xml' => $xml1
        ],
            vfsStream::setup('root')
        );
        $this->expectException(InvalidStateMachineFileException::class);
        $this->expectExceptionMessage("XML is not valid: Error 1871: Element 'states': Missing child element(s). Expected is ( state ).");
        $this->sut->build(
            $dir->url() . '/bad1.xml',
            true
        );
    }

    public function testValidatingInvalidXmlWillThrowAnExceptionForNumberOfTransitionsLessThanOne()
    {
        // count(transitions) < 1
        $xml1 = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<graph name="test" description="test xml stategraph">
    <states>
        <state name="start" description="Starting State"></state>
        <state name="build" description="Build state"></state>
    </states>
    <transitions>
    </transitions>
</graph>
XML;
        $dir = vfsStream::create([
            'bad1.xml' => $xml1
        ],
            vfsStream::setup('root')
        );
        $this->expectException(InvalidStateMachineFileException::class);
        $this->expectExceptionMessage("XML is not valid: Error 1871: Element 'transitions': Missing child element(s). Expected is ( transition ).");
        $this->sut->build(
            $dir->url() . '/bad1.xml',
            true
        );
    }

    public function testBuildingTheStategraphWillThrowAnExceptionIfGraphIsInvalid()
    {
        // count(transitions) < 1
        $xml1 = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<graph name="test" description="test xml stategraph">
    <states>
        <state name="start" description="Starting State"></state>
        <state name="build" description="Build state"></state>
    </states>
    <transitions>
        <transition name="building" description="beginning build" from="foo" to="bar"></transition>
    </transitions>
</graph>
XML;
        $dir = vfsStream::create([
            'bad1.xml' => $xml1
        ],
            vfsStream::setup('root')
        );
        $this->expectException(InvalidStateMachineFileException::class);
        $this->expectExceptionMessage("State: foo does not exist in graph");
        $this->sut->build(
            $dir->url() . '/bad1.xml',
            true
        );
    }

}
