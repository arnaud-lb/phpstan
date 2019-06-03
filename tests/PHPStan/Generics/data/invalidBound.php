<?php

namespace PHPStan\Generics\InvalidBound;

/**
 * @template T of int|float
 * @param T $a
 */
function functionA($a): void {
}
