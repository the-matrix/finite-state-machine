<?php
/**
 * Finite State Machine
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2018, UK
 * @license BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine;

use Chippyash\StateMachine\Exceptions\DuplicateStateException;
use Chippyash\StateMachine\Exceptions\DuplicateTransitionException;
use Chippyash\StateMachine\Exceptions\InvalidGraphException;
use Chippyash\StateMachine\Exceptions\InvalidStateException;
use Chippyash\StateMachine\Exceptions\InvalidTransitionException;
use Chippyash\StateMachine\Interfaces\Describable;
use Chippyash\StateMachine\Interfaces\StateAware;
use Chippyash\StateMachine\Traits\Describing;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

/**
 * Class StateGraph: A Directed Graph
 *
 * Composites Graph class
 */
class StateGraph implements Describable
{
    use Describing;

    /**
     * @var Graph
     */
    protected $graph;
    /**
     * Local cache for states.  Required to persists states between method calls
     * @var States
     */
    protected $states;
    /**
     * Local cache for graph Vertices. Required to persists transitions between
     * method calls
     * @var array [name => Vertice]
     */
    private $vertices = [];
    /**
     * Local cache for transitions. Required to persists transitions between method
     * calls
     * @var Transitions
     */
    protected $transitions;
    /**
     * Local storage for graph edges.  Required to persists transitions between
     * method calls
     * @var array [name => Directed]
     */
    private $edges = [];

    /**
     * StateGraph constructor.
     *
     * @param string      $name         Name of graph
     * @param string|null $description  Description of a StateGraph
     */
    public function __construct(string $name, ?string $description = '')
    {
        $this->name = $name;
        $this->description = $description;
        $this->graph = new Graph();
        $this->states = new States([]);
        $this->transitions = new Transitions([]);
    }

    /**
     * Add a State
     *
     * @param string $state Name of state
     *
     * @return StateGraph
     *
     * @throws DuplicateStateException
     */
    public function addState(State $state): StateGraph
    {
        if (array_key_exists($state->getName(), $this->states)) {
            throw new DuplicateStateException("'{$state->getName()}' is a duplicate");
        }

        $vertex = $this->graph->createVertex($state->getName());
        if (!empty($state->getDescription())) {
            $vertex->setAttribute('graphviz.label', $state->getDescription());
        }
        $this->vertices[$state->getName()] = $vertex;
        $this->states = $this->states->append([$state->getName() => $state]);

        return $this;
    }

    /**
     * Add a transition
     *
     * @param State      $from       State we are coming from
     * @param State      $to         State we are going to
     * @param Transition $transition Name of transition
     *
     * @return StateGraph
     *
     * @throws DuplicateTransitionException
     * @throws InvalidGraphException
     */
    public function addTransition(State $from, State $to, Transition $transition): StateGraph
    {
        if (array_key_exists($transition->getName(), $this->transitions)) {
            throw new DuplicateTransitionException("'{$transition->getName()}' is a duplicate");

        }
        if (!array_key_exists($from->getName(), $this->states)) {
            throw new InvalidGraphException("State: {$from->getName()} does not exist in graph");
        }
        if (!array_key_exists($to->getName(), $this->states)) {
            throw new InvalidGraphException("State: {$to->getName()} does not exist in graph");
        }

        /** @var Directed $edge */
        $edge = $this->vertices[$from->getName()]
            ->createEdgeTo($this->vertices[$to->getName()]);
        $edge->setAttribute('graphviz.id', $transition->getName());
        if (!empty($transition->getDescription())) {
            $edge->setAttribute('graphviz.label', $transition->getDescription());
        }
        $this->edges[$transition->getName()] = $edge;
        $this->transitions = $this->transitions->append([$transition->getName() => $transition]);

        return $this;
    }

    /**
     * Return next available transitions for a state
     *
     * @param StateAware $statefulObject
     *
     * @return Transitions
     */
    public function getTransitionsForState(StateAware $statefulObject): Transitions
    {
        $transitions = [];
        /** @var Directed $edge */
        foreach ($this->vertices[$statefulObject->getState()->getName()]->getEdgesOut() as $edge) {
            $transitions[$edge->getAttribute('graphviz.id')] = $this->transitions[$edge->getAttribute('graphviz.id')];
        }

        return new Transitions($transitions);
    }

    /**
     * Is the state an initial state, i.e. no incoming transitions
     *
     * @param State $state
     *
     * @return bool
     */
    public function isInitialState(State $state): bool
    {
        return $this->vertices[$state->getName()]->getEdgesIn()->count() == 0;
    }

    /**
     * Is the state the final state, i.e. no outgoing transitions
     *
     * @param State $state
     *
     * @return bool
     */
    public function isFinalState(State $state): bool
    {
        return $this->vertices[$state->getName()]->getEdgesOut()->count() == 0;
    }

    /**
     * Return the initial states for the Graph
     *
     * @return States
     *
     * @throws InvalidGraphException
     */
    public function getInitialStates(): States
    {
        $states = [];
        /** @var Vertex $vertex */
        foreach ($this->vertices as $vertex) {
            if ($vertex->getEdgesIn()->count() == 0) {
                $states[$vertex->getId()] = $this->states[$vertex->getId()];
            }
        }
        if (count($states) == 0) {
            throw new InvalidGraphException('No initial state found for graph');
        }

        return new States($states);
    }

    /**
     * Get the State that the transition moves to
     *
     * @param Transition $transition
     *
     * @return State
     */
    public function getNextStateForTransition(Transition $transition): State
    {
        return $this->states[$this->edges[$transition->getName()]->getVertexEnd()->getId()];
    }

    /**
     * Is the graph valid:
     *
     * - no unconnected states
     * - minimum of 1 initial state
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $noInTrans = 0;

        /** @var Vertex $vertex */
        foreach ($this->vertices as $vertex) {
            //case: state has no in or out transitions
            if ($vertex->getEdges()->count() == 0) {
                return false;
            }
            $noInTrans += ($vertex->getEdgesIn()->count() == 0) ? 1 : 0;
        }

        if ($noInTrans == 0) {
            return false;
        }

        return true;
    }

    /**
     * Return the composited Graph
     *
     * @return Graph
     */
    public function getGraph(): Graph
    {
        return $this->graph->createGraphClone();
    }

    /**
     * Return all known transitions
     *
     * @return Transitions
     */
    public function getTransitions(): Transitions
    {
        return $this->transitions;
    }

    /**
     * Return all known states
     *
     * @return States
     */
    public function getStates(): States
    {
        return $this->states;
    }

    /**
     * Transition an object to another State
     *
     * @param StateAware $object
     * @param Transition $transition
     *
     * @return StateGraph
     *
     * @throws InvalidStateException
     * @throws InvalidTransitionException
     */
    public function transition(StateAware $object, Transition $transition): StateGraph
    {
        $objectStateName = $object->getState()->getName();
        if (!array_key_exists($objectStateName, $this->states)) {
            throw new InvalidStateException("State: {$objectStateName} does not exist in StateGraph");
        }
        $transitionName = $transition->getName();
        if (!array_key_exists($transitionName, $this->transitions)) {
            throw new InvalidTransitionException("Transition: {$transitionName} does not exist in StateGraph");
        }
        $availableTransitions = $this->getTransitionsForState($object);
        if (!array_key_exists($transitionName, $availableTransitions)) {
            throw new InvalidTransitionException("Transition: {$transitionName} is not available for State: {$objectStateName}");
        }

        //@todo Add capability to check entry and exit conditions etc
        $nextState = $this->getNextStateForTransition($transition);
        $object->setState($nextState);

        return $this;
    }

    /**
     * Proxy to underlying Graph object;
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        if (!method_exists($this->graph, $method)) {
            throw new \BadMethodCallException("Method: {$method} does not exist on StateGraph");
        }

        return call_user_func_array([$this->graph, $method], $arguments);
    }
}