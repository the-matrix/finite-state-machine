<?php
/**
 * Finite State Machine
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2018, UK
 * @license BSD 3 Clause See LICENSE.md
 */
namespace Test\Chippyash\StateMachine;

use Chippyash\StateMachine\Events\StateGraphEvent;
use Chippyash\StateMachine\Events\StateGraphEventType;
use Chippyash\StateMachine\Exceptions\DuplicateStateException;
use Chippyash\StateMachine\Exceptions\DuplicateTransitionException;
use Chippyash\StateMachine\Exceptions\InvalidGraphException;
use Chippyash\StateMachine\Exceptions\InvalidStateException;
use Chippyash\StateMachine\Exceptions\InvalidTransitionException;
use Chippyash\StateMachine\Interfaces\Describable;
use Chippyash\StateMachine\Interfaces\StateAware;
use Chippyash\StateMachine\State;
use Chippyash\StateMachine\StateGraph;
use Chippyash\StateMachine\States;
use Chippyash\StateMachine\Transition;
use Chippyash\StateMachine\Transitions;
use Fhaculty\Graph\Attribute\AttributeBag;
use Fhaculty\Graph\Graph;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class StateGraphTest extends TestCase
{
    /**
     * @var StateGraph
     */
    protected $sut;

    public function testYouCanRetrieveAStategraphNameAndDescription()
    {
        $this->assertEquals('test', $this->sut->getName());
        $this->assertEquals('description', $this->sut->getDescription());
    }

    public function testAStategraphImplementsTheDescribableInterface()
    {
        $this->assertInstanceOf(Describable::class, $this->sut);
    }

    public function testYouCanCreateAStategraph()
    {
        $this->sut->addState(new State('state1'))
            ->addState(new State('state2'))
            ->addState(new State('state3'))
            ->addTransition(new State('state1'), new State('state2'), new Transition('pending'))
            ->addTransition(new State('state2'), new State('state3'), new Transition('complete'));

        $this->assertEquals(3, $this->sut->getVertices()->count());
        $this->assertEquals(2, $this->sut->getEdges()->count());
    }

    public function testAddingDuplicateStatesWillThrowAnException()
    {
        $this->expectException(DuplicateStateException::class);
        $this->sut->addState(new State('state1'))
            ->addState(new State('state1'));
    }

    public function testAddingDuplicateTransitionsWillThrowAnException()
    {
        $this->expectException(DuplicateTransitionException::class);
        $this->sut->addState(new State('state1'))
            ->addState(new State('state2'))
            ->addTransition(new State('state1'), new State('state2'), new Transition('pending'))
            ->addTransition(new State('state1'), new State('state2'), new Transition('pending'));
    }

    public function testAddingTransitionsForNonExistentFromStateWillThrowAnException()
    {
        $this->sut->addState(new State('state1'))
            ->addState(new State('state2'));
        $this->expectException(InvalidGraphException::class);
        $this->sut->addTransition(new State('unknown'), new State('state2'), new Transition('test'));
    }

    public function testAddingTransitionsForNonExistentToStateWillThrowAnException()
    {
        $this->sut->addState(new State('state1'))
            ->addState(new State('state2'));
        $this->expectException(InvalidGraphException::class);
        $this->sut->addTransition(new State('state2'), new State('unknown'), new Transition('test'));
    }


    public function testYouCanRetrievePossibleTransitionsForAState()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $pending = new Transition('pending');
        $redo = new Transition('redo');
        $complete = new Transition('complete');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3)
            ->addTransition($state1, $state2, $pending)
            ->addTransition($state2, $state1, $redo)
            ->addTransition($state2, $state3, $complete);

        $object = $this->createMock(StateAware::class);
        $object->expects($this->once())
            ->method('getState')
            ->willReturn($state2);

        $this->assertEquals(['redo', 'complete'], array_keys($this->sut->getTransitionsForState($object)->toArray()));
    }

    public function testYouCanTestIfStateIsAnInitialState()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $pending = new Transition('pending');
        $complete = new Transition('complete');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3)
            ->addTransition($state1, $state2, $pending)
            ->addTransition($state2, $state3, $complete);

        $this->assertTrue($this->sut->isInitialState($state1));
        $this->assertFalse($this->sut->isInitialState($state3));
    }

    public function testYouCanTestIfStateIsAFinalState()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $pending = new Transition('pending');
        $complete = new Transition('complete');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3)
            ->addTransition($state1, $state2, $pending)
            ->addTransition($state2, $state3, $complete);

        $this->assertTrue($this->sut->isFinalState($state3));
        $this->assertFalse($this->sut->isFinalState($state1));
    }

    public function testYouCanRetrieveTheInitialStates()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $pending = new Transition('pending');
        $complete = new Transition('complete');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3)
            ->addTransition($state1, $state2, $pending)
            ->addTransition($state2, $state3, $complete);

        $this->assertEquals(['state1'], array_keys($this->sut->getInitialStates()->toArray()));
    }

    public function testAnExceptionWillBeThrownIfThereAreNoInitialStatesToBeRetrieved()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $pending = new Transition('pending');
        $redo = new Transition('redo');
        $complete = new Transition('complete');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3)
            ->addTransition($state1, $state2, $pending)
            ->addTransition($state2, $state1, $redo)
            ->addTransition($state2, $state3, $complete);

        $this->expectException(InvalidGraphException::class);
        $this->sut->getInitialStates();
    }

    public function testYouCanValidateAStategraph()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $pending = new Transition('pending');
        $complete = new Transition('complete');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3)
            ->addTransition($state1, $state2, $pending)
            ->addTransition($state2, $state3, $complete);

        $this->assertTrue($this->sut->isValid());
    }

    public function testAStategraphIsInvalidIfItHasNoTransitions()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3);

        $this->assertFalse($this->sut->isValid());
    }

    public function testAStategraphIsInvalidIfItHasNoInitialTransition()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $pending = new Transition('pending');
        $redo = new Transition('redo');
        $complete = new Transition('complete');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3)
            ->addTransition($state1, $state2, $pending)
            ->addTransition($state2, $state1, $redo)
            ->addTransition($state2, $state3, $complete);

        $this->assertFalse($this->sut->isValid());
    }

    public function testYouCanRetrieveACloneOfTheCompositedGraphObject()
    {
        $this->assertInstanceOf(Graph::class, $this->sut->getGraph());
    }

    public function testAddingAStateWithADescriptionWillAddALabelToTheGraphVertex()
    {
        $this->sut->addState(new State('test', 'foobar'));
        $graph = $this->sut->getGraph();
        $vertex = $graph->getVertex('test');

        $this->assertEquals('test', $vertex->getId());
        $this->assertEquals('foobar', $vertex->getAttribute('graphviz.label'));
    }

    public function testAddingATransitionWithADescriptionWillAddALabelToTheGraphEdge()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addTransition($state1, $state2, new Transition('test', 'foobar'));
        $graph = $this->sut->getGraph();
        $edge = $graph->getEdges()->getEdgeFirst();

        $this->assertEquals('test', $edge->getAttribute('graphviz.id'));
        $this->assertEquals('foobar', $edge->getAttribute('graphviz.label'));
    }

    public function testYouCanGetTheNextStateForATransition()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $transition = new Transition('test', 'foobar');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addTransition($state1, $state2, $transition);

        $this->assertEquals($state2, $this->sut->getNextStateForTransition($transition));
    }

    public function testYouCanRetrieveAllTransitions()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $transition1 = new Transition('test1');
        $transition2 = new Transition('test2');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addTransition($state1, $state2, $transition1)
            ->addTransition($state1, $state2, $transition2);

        $transitions = $this->sut->getTransitions();
        $this->assertInstanceOf(Transitions::class, $transitions);
        $this->assertEquals(2, $transitions->count());
    }

    public function testYouCanRetrieveAllStates()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $this->sut->addState($state1)
            ->addState($state2);

        $states = $this->sut->getStates();
        $this->assertInstanceOf(States::class, $states);
        $this->assertEquals(2, $states->count());

    }

    public function testAStategraphSupportsProxyMethodCallsToTheUnderlyingGraphObject()
    {
        $attributes = $this->sut->getAttributeBag();
        $this->assertInstanceOf(AttributeBag::class, $attributes);
    }

    public function testAnExceptionIsThrownForAnUnknownProxyMethod()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->sut->fooBar();
    }
    
    public function testYouCanTransitionAnObjectStateWithAStategraph()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $transition1 = new Transition('test1');
        $transition2 = new Transition('test2');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addTransition($state1, $state2, $transition1)
            ->addTransition($state1, $state2, $transition2);

        $object = $this->createMock(StateAware::class);
        $object->expects($this->exactly(2))
            ->method('getState')
            ->willReturn($state1);
        $object->expects($this->once())
            ->method('setState')
            ->with($state2);

        $this->sut->transition($object, $transition1);
    }

    public function testYouCannotTransitionAnObjectFromAnUnknownState()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $transition1 = new Transition('test1');
        $transition2 = new Transition('test2');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addTransition($state1, $state2, $transition1)
            ->addTransition($state1, $state2, $transition2);

        $object = $this->createMock(StateAware::class);
        $object->expects($this->exactly(1))
            ->method('getState')
            ->willReturn(new State('unknown'));

        $this->expectException(InvalidStateException::class);
        $this->sut->transition($object, $transition1);
    }

    public function testYouCannotTransitionAnObjectWithAnUnknownTransition()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $transition1 = new Transition('test1');
        $transition2 = new Transition('unknown');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addTransition($state1, $state2, $transition1);

        $object = $this->createMock(StateAware::class);
        $object->expects($this->exactly(1))
            ->method('getState')
            ->willReturn($state1);

        $this->expectException(InvalidTransitionException::class);
        $this->sut->transition($object, $transition2);
    }

    public function testYouCannotTransitionAnObjectIfTheTransitionDoesNotApplyToTheCurrentObjectState()
    {
        $state1 = new State('state1');
        $state2 = new State('state2');
        $state3 = new State('state3');
        $transition1 = new Transition('test1');
        $transition2 = new Transition('test2');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addState($state3)
            ->addTransition($state1, $state2, $transition1)
            ->addTransition($state2, $state3, $transition2);

        $object = $this->createMock(StateAware::class);
        $object->expects($this->exactly(2))
            ->method('getState')
            ->willReturn($state1);

        $this->expectException(InvalidTransitionException::class);
        $this->sut->transition($object, $transition2);
    }

    protected function setUp()
    {
        $this->sut = new StateGraph('test', 'description');
    }
}
