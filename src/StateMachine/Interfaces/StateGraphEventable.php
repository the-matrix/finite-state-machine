<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Interfaces;

use Chippyash\StateMachine\Events\StateGraphEvent;
use Chippyash\StateMachine\Events\StateGraphEventType;
use Chippyash\StateMachine\Transition;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Interface for a PSR-14 event that can be consumed by the StateGraph
 */
interface StateGraphEventable extends StoppableEventInterface
{
    /**
     * @return Transition
     */
    public function getStateGraphTransition(): Transition;

    /**
     * @return StateAware
     */
    public function getStateGraphObject(): StateAware;

    /**
     * Set the propagation flag
     *
     * @param bool $flag
     *
     * @return StateGraphEvent
     */
    public function setPropagationStopped(bool $flag): StateGraphEvent;

    /**
     * @return StateGraphEventType
     */
    public function getEventType(): StateGraphEventType;

    /**
     * Set 'has been processed marker flag'
     *
     * @param bool $flag
     *
     * @return StateGraphEvent
     */
    public function setProcessMarker(bool $flag): StateGraphEvent;

    /**
     * Get the process marker flag
     *
     * @return bool
     */
    public function getProcessmarker(): bool;
}