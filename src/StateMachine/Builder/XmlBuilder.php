<?php
/**
 * Finite State Machine
 *
 * @author    Ashley Kitson
 * @copyright Ashley Kitson, 2019, UK
 * @license   BSD 3 Clause See LICENSE.md
 */
namespace Chippyash\StateMachine\Builder;

use Assembler\FFor;
use Chippyash\StateMachine\Exceptions\InvalidStateMachineFileException;
use Chippyash\StateMachine\Exceptions\StateMachineException;
use Chippyash\StateMachine\Interfaces\StateMachineBuilder;
use Chippyash\StateMachine\State;
use Chippyash\StateMachine\StateGraph;
use Chippyash\StateMachine\States;
use Chippyash\StateMachine\Transition;
use Chippyash\StateMachine\Transitions;

/**
 * Builder for StateGraph from XML
 */
class XmlBuilder implements StateMachineBuilder
{
    /**
     * Build a StateGraph Machine from an XML source file
     *
     * @param string    $file     Source file
     * @param bool      $validate Validate the file? Default == No
     *
     * @return StateGraph
     * @throws InvalidStateMachineFileException
     */
    public function build(string $file, bool $validate = false): StateGraph
    {
        return FFor::create(['xmlFile' => $file, 'validate' => $validate])
            ->dom(function(string $xmlFile, bool $validate): \DOMDocument {
                if (!file_exists($xmlFile)) {
                    throw new InvalidStateMachineFileException("XML StateMachine source file: {$xmlFile} does not exist");
                }
                $dom = new \DOMDocument();
                $dom->load($xmlFile);
                $dom->preserveWhiteSpace = false;
                if ($validate && !$dom->schemaValidate(__DIR__ . '/statemachine.xsd')) {
                    throw new InvalidStateMachineFileException('XML is not valid');
                }
                return $dom;
            })

            ->xpath(function(\DOMDocument $dom): \DOMXPath {
                return new \DOMXPath($dom);
            })

            ->states(function(\DOMDocument $dom, \DOMXPath $xpath): States {
                $nodes = $xpath->query('//state');
                $states = [];
                /** @var \DOMNode $node */
                foreach($nodes as $node) {
                    $states[] = new State($node->attributes->getNamedItem('name'), $node->attributes->getNamedItem('description'));
                }

                return new States($states);
            })

            ->transitions(function(\DOMDocument $dom, \DOMXPath $xpath): Transitions {
                $nodes = $xpath->query('//transition');
                $transitions = [];
                /** @var \DOMNode $node */
                foreach($nodes as $node) {
                    $transitions[] = (new Transition(
                        $node->attributes->getNamedItem('name'),
                        $node->attributes->getNamedItem('description')
                    ))
                        ->setFromStateName($node->attributes->getNamedItem('from'))
                        ->setToStateName($node->attributes->getNamedItem('to'));
                }

                return new Transitions($transitions);
            })

            ->stateGraph(function(States $states, Transitions $transitions, \DOMXPath $xpath): StateGraph {
                $node = $xpath->query('//graph')->item(0);
                $stateGraph = new StateGraph(
                    $node->attributes->getNamedItem('name'),
                    $node->attributes->getNamedItem('description')
                );
                try {
                    /** @var State $state */
                    foreach ($states as $state) {
                        $stateGraph->addState($state);
                    }
                } catch (StateMachineException $e) {
                    throw new InvalidStateMachineFileException($e->getMessage(), $e->getCode(), $e);
                }

                try {
                    /** @var Transition $transition */
                    foreach ($transitions as $transition) {
                        $stateGraph->addTransition(
                            $states[$transition->getFromStateName()],
                            $states[$transition->getToStateName()],
                            $transition
                        );
                    }
                } catch (StateMachineException $e) {
                    throw new InvalidStateMachineFileException($e->getMessage(), $e->getCode(), $e);
                }

                return $stateGraph;
            })
            ->fyield('stateGraph');
    }
}