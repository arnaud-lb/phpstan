<?php

namespace PHPStan\Generic\Functions;

use function PHPStan\Generic\assertType;

/**
 * @template T
 * @param T $a
 * @return T
 */
function functionA($a) {
	return $a;
}

/**
 * @param int|float $intFloat
 * @param mixed $mixed
 */
function functionATest($intFloat, $mixed) {
	assertType('int', functionA(1));
	assertType('int|float', functionA($intFloat));
	assertType('DateTime', functionA(new \DateTime()));
	assertType('mixed', functionA($mixed));
}

/**
 * @template T of \DateTimeInterface
 * @param T $a
 * @return T
 */
function functionB($a) {
	return $a;
}

/**
 * @param \DateTimeInterface $datetime
 */
function assertTypeTest($dateTimeInterface) {
	assertType('DateTime', functionB(new \DateTime()));
	assertType('DateTimeImmutable', functionB(new \DateTimeImmutable()));
	assertType('DateTimeInterface', functionB($datetime));
}

/**
 * @template K
 * @template V
 * @param array<K,V> $a
 * @return array<K,V>
 */
function functionC($a) {
	return $a;
}

function functionCTest() {
	assertType('array<int,string>', functionC(['x']));
}

/**
 * @template T
 * @param T $a
 * @param T $b
 * @return T
 */
function functionD($a, $b) {
	return $a;
}

/**
 * @param int|float $intFloat
 */
function functionDTest($intFloat) {
	assertType('int', functionD(1, 1));
	assertType('int|float', functionD(1, 1.0));
	assertType('int|DateTime', functionD(1, new \DateTime()));
	assertType('int|float|DateTime', functionD($intFloat, new \DateTime()));
	assertType('array|DateTime', functionD([], new \DateTime()));
}

/**
 * @template T
 * @param array<\DateTime|array<T>> $a
 * @return T
 */
function functionE($a) {
	return $a;
}

/**
 * @param int|float $intFloat
 */
function functionETest($intFloat) {
	assertType('int', functionE([[1]]));
}

