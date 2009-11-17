#!/bin/sh

phpunit \
	--verbose \
	--color \
	--coverage-html ./unittest_coverage \
	$@ \
all_tests.php
