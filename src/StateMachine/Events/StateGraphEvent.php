<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Events;

use Chippyash\StateMachine\Interfaces\StateAware;
use Chippyash\StateMachine\Interfaces\StateGraphEventable;
use Chippyash\StateMachine\Traits\StateGraphEventing;
use Chippyash\StateMachine\Transition;

/**
 * An event message acceptable to the StateGraph::eventListener method
 */
class StateGraphEvent implements StateGraphEventable
{
    use StateGraphEventing;

    /**
     * StateGraphEvent constructor.
     *
     * @param Transition $transition
     * @param StateAware $object
     * @param StateGraphEventType|null $eventType Default is DO_TRANSITION
     */
    public function __construct(Transition $transition, StateAware $object, ?StateGraphEventType $eventType = null)
    {
        $this->stateGraphtransition = $transition;
        $this->stateGraphobject = $object;
        $this->stateGraphEventType = $eventType ?? StateGraphEventType::DO_TRANSITION();
    }
}