<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine;

use Monad\Map;

/**
 * A Map of State objects
 */
class States extends Map
{
    public function __construct(array $value = [])
    {
        $statesNames = array_map(
            function(State $state) { return $state->getName();},
            $value
        );
        parent::__construct(array_combine($statesNames, $value), State::class);
    }
}