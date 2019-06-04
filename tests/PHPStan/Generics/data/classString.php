<?php

namespace PHPStan\Generics\ClassString;

/**
 * @template T
 * @param class-string<T> $a
 * @return T
 */
function a($a) {
	return new $a();
}

function testA() {
	a('stdClass');
	a('DateTime');
	a(1);
}
