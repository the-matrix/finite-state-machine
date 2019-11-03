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
 * A Map of Transition
 */
class Transitions extends Map
{
    public function __construct(array $value = [])
    {
        $transitionNames = array_map(
            function(Transition $transition) {return $transition->getName();},
            $value
        );
        parent::__construct(array_combine($transitionNames, $value), Transition::class);
    }
}