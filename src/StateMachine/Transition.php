<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine;

use Chippyash\StateMachine\Interfaces\Describable;
use Chippyash\StateMachine\Traits\Describing;

class Transition implements Describable
{
    use Describing;

    /**
     * Transition constructor.
     *
     * @param string $name
     */
    public function __construct(string $name, ?string $description = '')
    {
        $this->name = $name;
        $this->description = $description;
    }
}