#!/bin/sh

phpunit \
	--verbose \
	--color \
	--coverage-html ./unittest_coverage \
	--log-tap ./unittest_tap_log.txt \
all_tests.php
