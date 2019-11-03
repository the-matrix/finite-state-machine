#!/bin/bash
tdconv="/home/akitson/tdconv/bin/tdconv"  || exit
cd D:\Chippyash\cl-state-machine
vendor/phpunit/phpunit/phpunit -c ./phpunit.xml --testdox-html contract.html test/StateMachine
${tdconv} -t "Chippyash Finite State Machine" contract.html docs/Test-Contract.md
rm contract.html