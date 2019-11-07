<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Traits;

use Chippyash\StateMachine\Exceptions\InvalidStateException;
use Chippyash\StateMachine\Interfaces\StateAware;
use Chippyash\StateMachine\State;

/**
 * Trait that implements StateAware
 */
trait HasState
{
    /**
     * @var State
     */
    protected $state;

    public function getState(): State
    {
        if (empty($this->state)) {
            throw new InvalidStateException('Object has no state');
        }
        return $this->state;
    }

    public function setState(State $state): StateAware
    {
        $this->state = $state;
        return $this;
    }
}