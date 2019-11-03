<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Test\Chippyash\StateMachine\Events;

use Chippyash\StateMachine\Events\EventableStateGraph;
use Chippyash\StateMachine\Events\StateGraphEvent;
use Chippyash\StateMachine\Events\StateGraphEventType;
use Chippyash\StateMachine\Interfaces\StateAware;
use Chippyash\StateMachine\State;
use Chippyash\StateMachine\Transition;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventableStateGraphTest extends TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    /**
     * @var EventableStateGraph
     */
    protected $sut;

    public function testAStategraphCanListenForPsr14Events()
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

        $event = $this->sut->eventListener(new StateGraphEvent($transition1, $object));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertTrue($event->getProcessmarker());
    }

    public function testErrorsFromTransitioningViaAnEventWillStopPropagationForTheEvent()
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

        $event = $this->sut->eventListener(new StateGraphEvent($transition2, $object));
        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->getProcessmarker());
    }

    public function testTheListenerOnlyListensForTransitionEvents()
    {
        $transition = new Transition('test');
        $object = $this->createMock(StateAware::class);

        $event = $this->sut->eventListener(
            new StateGraphEvent($transition, $object, StateGraphEventType::START_TRANSITION())
        );
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->getProcessmarker());
    }

    public function testYouCanSetAPsr14EventDispatcherOnTheStategraph()
    {
        $this->sut->setEventDispatcher($this->dispatcher);
        //using reflection as property is protected
        $refl = new \ReflectionObject($this->sut);
        $reflProp = $refl->getProperty('eventDispatcher');
        $reflProp->setAccessible(true);
        $value = $reflProp->getValue($this->sut);

        $this->assertEquals($this->dispatcher, $value);
    }

    public function testTheEventListenerWillTriggerTransitionEvents()
    {
        $this->sut->setEventDispatcher($this->dispatcher);

        $state1 = new State('state1');
        $state2 = new State('state2');
        $transition1 = new Transition('test1');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addTransition($state1, $state2, $transition1);

        $object = $this->createMock(StateAware::class);
        $object->expects($this->exactly(2))
            ->method('getState')
            ->willReturn($state1);
        $object->expects($this->once())
            ->method('setState')
            ->with($state2);

        $this->dispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->sut->eventListener(new StateGraphEvent($transition1, $object));
    }

    public function testPreTransitionEventsCanStopATransitionFromOccurring()
    {
        $this->sut->setEventDispatcher($this->dispatcher);

        $state1 = new State('state1');
        $state2 = new State('state2');
        $transition1 = new Transition('test1');
        $this->sut->addState($state1)
            ->addState($state2)
            ->addTransition($state1, $state2, $transition1);

        $object = $this->createMock(StateAware::class);

        $event = $this->sut->eventListener(
            (new StateGraphEvent($transition1, $object))->setPropagationStopped(true)
        );
        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->getProcessmarker());
    }

    protected function setUp()
    {
        $this->sut = new EventableStateGraph('test', 'description');
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }
}
