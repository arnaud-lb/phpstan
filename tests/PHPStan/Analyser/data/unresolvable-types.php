<?php

namespace UnresolvableTypes;

/**
 * @param array<int, int, int> $arrayWithTooManyArgs
 * @param iterable<int, int, int> $iterableWithTooManyArgs
 */
function test(
	$arrayWithTooManyArgs,
	$iterableWithTooManyArgs
) {
	die;
}
