<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2018, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine;

use Chippyash\StateMachine\Interfaces\Describable;
use Chippyash\StateMachine\Traits\Describing;

/**
 * A state in a StateGraph
 */
class State implements Describable
{
    use Describing;

    /**
     * State constructor.
     *
     * @param string $name
     * @param string $description
     */
    public function __construct(string $name, ?string $description = '')
    {
        $this->name = $name;
        $this->description = $description;
    }
}