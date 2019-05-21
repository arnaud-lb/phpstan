<?php

namespace PHPStan\Generics\FailToInfer;

use function PHPStan\Generics\assertType;

/**
 * @template T
 * @param array<T>|\DateTime $a
 */
function functionA($a) {
}

function functionATest() {
	functionA(new \DateTime());
}

