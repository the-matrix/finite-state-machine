<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Interfaces;

use Chippyash\StateMachine\Exceptions\InvalidStateException;
use Chippyash\StateMachine\State;

/**
 * Interface for a class that has a StateGraph State
 */
interface StateAware
{
    /**
     * Get Object State
     *
     * Exception thrown if no state is set
     *
     * @return string
     *
     * @throws InvalidStateException
     */
    public function getState(): State;

    /**
     * Set Object State
     *
     * @param State $state
     *
     * @return Stateful
     */
    public function setState(State $state): StateAware;
}