<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Events;

use Chippyash\StateMachine\Exceptions\StateMachineException;
use Chippyash\StateMachine\Interfaces\StateGraphEventable;
use Chippyash\StateMachine\StateGraph;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * A StetGraph that is PSR-14 Event Aware
 */
class EventableStateGraph extends StateGraph
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Listen for Transition Events and act on them
     *
     * Dispatch start and end transition events if we have an event dispatcher
     *
     * @param StateGraphEventable $event
     *
     * @return StateGraphEventable
     */
    public function eventListener(StateGraphEventable $event): StateGraphEventable
    {
        if (!$event->getEventType()->equals(StateGraphEventType::DO_TRANSITION())) {
            return $event;
        }

        $event->setProcessMarker(false);

        //Handle validations, conditions etc
        $preEvent = $this->dispatchAnotherEvent($event, StateGraphEventType::START_TRANSITION());
        if ($preEvent->isPropagationStopped()) {
            //stop the transition
            return $event->setPropagationStopped(true);
        }

        try {
            $this->transition($event->getStateGraphObject(), $event->getStateGraphTransition());
            $event->setProcessMarker(true);
        } catch (StateMachineException $e) {
            $event->setPropagationStopped(true);
        }

        //do any post transition handling
        $this->dispatchAnotherEvent($event, StateGraphEventType::END_TRANSITION());

        return $event;
    }


    /**
     * Set a PSR-14 compatible Event Dispatcher
     *
     * @param EventDispatcherInterface $dispatcher
     *
     * @return StateGraph
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher): StateGraph
    {
        $this->eventDispatcher = $dispatcher;
        return $this;
    }

    /**
     * @param StateGraphEventable $previousEvent
     * @param StateGraphEventType $eventType
     *
     * @return StateGraphEvent
     */
    protected function dispatchAnotherEvent(StateGraphEventable $previousEvent, StateGraphEventType $eventType): StateGraphEvent
    {
        if (empty($this->eventDispatcher)) {
            return $previousEvent;
        }
        $event = (new StateGraphEvent(
            $previousEvent->getStateGraphTransition(),
            $previousEvent->getStateGraphObject(),
            $eventType
        ))
            ->setProcessMarker($previousEvent->getProcessmarker())
            ->setPropagationStopped($previousEvent->isPropagationStopped());

        $this->eventDispatcher->dispatch($event);

        return $event;
    }
}