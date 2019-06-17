<?php

namespace PHPStan\Generics\InvalidBound;

/**
 * @template T of int|float
 * @param T $a
 */
function a($a): void {
}

class C
{
	/**
	 * @template T of int|float
	 * @param T $a
	 */
	public function a($a)
	{
	}
}
