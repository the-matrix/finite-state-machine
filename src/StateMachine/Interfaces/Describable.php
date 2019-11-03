<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Interfaces;

/**
 * Interface Describable
 *
 * An object that has a description
 */
interface Describable
{
    /**
     * Get object name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get object description
     *
     * @return string
     */
    public function getDescription(): string;
}