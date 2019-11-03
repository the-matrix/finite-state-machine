<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Traits;

use Chippyash\StateMachine\Events\StateGraphEvent;
use Chippyash\StateMachine\Events\StateGraphEventType;
use Chippyash\StateMachine\Interfaces\StateAware;
use Chippyash\StateMachine\Transition;

/**
 * Implements StateGraphEventable interface
 */
trait StateGraphEventing
{
    /**
     * @var Transition
     */
    protected $stateGraphtransition;
    /**
     * @var StateAware
     */
    protected $stateGraphobject;
    /**
     * @var bool
     */
    protected $stateProcessmarker = false;
    /**
     * @api StoppableEventInterface
     * @var bool
     */
    protected $propagationStopped = false;
    /**
     * @var StateGraphEventType
     */
    protected $stateGraphEventType;

    /**
     * @return Transition
     */
    final public function getStateGraphTransition(): Transition
    {
        return $this->stateGraphtransition;
    }

    /**
     * @return StateAware
     */
    final public function getStateGraphObject(): StateAware
    {
        return $this->stateGraphobject;
    }

    /**
     * Set the propagation flag
     *
     * @param bool $flag
     *
     * @return StateGraphEvent
     */
    final public function setPropagationStopped(bool $flag): StateGraphEvent
    {
        $this->propagationStopped = $flag;
        return $this;
    }

    /**
     * @api StoppableEventInterface
     * @return bool
     */
    final public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * @return StateGraphEventType
     */
    final public function getEventType(): StateGraphEventType
    {
        return $this->stateGraphEventType;
    }

    /**
     * Set 'has been processed marker flag'
     *
     * @param bool $flag
     *
     * @return StateGraphEvent
     */
    final public function setProcessMarker(bool $flag): StateGraphEvent
    {
        $this->stateProcessmarker = $flag;
        return $this;
    }

    /**
     * Get the process marker flag
     *
     * @return bool
     */
    final public function getProcessmarker(): bool
    {
        return $this->stateProcessmarker;
    }
}