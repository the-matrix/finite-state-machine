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
     * @var string
     */
    protected $fromStateName = '';
    /**
     * @var string
     */
    protected $toStateName = '';

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

    /**
     * @return string
     */
    public function getFromStateName(): string
    {
        return $this->fromStateName;
    }

    /**
     * @param string $fromStateName
     *
     * @return Transition
     */
    public function setFromStateName(string $fromStateName): Transition
    {
        $this->fromStateName = $fromStateName;

        return $this;
    }

    /**
     * @return string
     */
    public function getToStateName(): string
    {
        return $this->toStateName;
    }

    /**
     * @param string $toStateName
     *
     * @return Transition
     */
    public function setToStateName(string $toStateName): Transition
    {
        $this->toStateName = $toStateName;

        return $this;
    }

}