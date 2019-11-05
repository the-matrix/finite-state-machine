<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Interfaces;

use Chippyash\StateMachine\Exceptions\InvalidStateMachineFileException;
use Chippyash\StateMachine\StateGraph;

/**
 * Interface for a builder class that can build a StateMachine
 */
interface StateMachineBuilder
{
    /**
     * Build a StateGraph Machine from a source file
     *
     * @param string    $file     Source file
     * @param bool      $validate Validate the file? Default == No
     *
     * @return StateGraph
     * @throws InvalidStateMachineFileException
     */
    public function build(string $file, bool $validate = false): StateGraph;
}