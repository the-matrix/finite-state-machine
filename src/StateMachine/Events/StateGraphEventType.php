<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Events;

use MyCLabs\Enum\Enum;

/**
 * Class StateGraphEventType
 *
 * @method static StateGraphEventType DO_TRANSITION()
 * @method static StateGraphEventType START_TRANSITION()
 * @method static StateGraphEventType END_TRANSITION()
 */
class StateGraphEventType extends Enum
{
    const DO_TRANSITION = 1;
    const START_TRANSITION = 2;
    const END_TRANSITION = 3;
}