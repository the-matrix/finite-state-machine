<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Interfaces;

use Chippyash\StateMachine\State;

/**
 * Interface for a class that has a StateGraph State
 */
interface StateAware
{
    /**
     * Get Object State
     *
     * @return string
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