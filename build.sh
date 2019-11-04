#!/bin/bash
tdconv="/home/akitson/tdconv/bin/tdconv"  || exit
vendor/phpunit/phpunit/phpunit -c ./phpunit.xml --testdox-html contract.html test/StateMachine
${tdconv} -t "Chippyash Finite State Machine" contract.html docs/Test-Contract.md
rm contract.html