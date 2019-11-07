<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Test\Chippyash\StateMachine\Traits;

use Chippyash\StateMachine\Interfaces\StateAware;
use Chippyash\StateMachine\State;
use Chippyash\StateMachine\Traits\HasState;
use PHPUnit\Framework\TestCase;

class HasStateStub implements StateAware {
    use HasState;
}

class HasStateTest extends TestCase
{
    /**
     * @var HasState
     */
    protected $sut;

    protected function setUp()
    {
        $this->sut = new HasStateStub();

    }

    public function testYouCanTestForHavingState()
    {
        $this->assertFalse($this->sut->hasState());
        $this->sut->setState(new State('test'));
        $this->assertTrue($this->sut->hasState());
    }

    public function testYouCanSetAndGetTheState()
    {
        $state = new State('test');
        $this->sut->setState($state);
        $this->assertEquals($state, $this->sut->getState());
    }
}
