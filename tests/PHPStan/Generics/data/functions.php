<?php

namespace PHPStan\Generics\Functions;

use function PHPStan\Generics\assertType;

/**
 * @template T
 * @param T $a
 * @return T
 */
function functionA($a) {
	return $a;
}

/**
 * @param int $int
 * @param int|float $intFloat
 * @param mixed $mixed
 */
function functionATest($int, $intFloat, $mixed) {
	assertType('int', functionA($int));
	assertType('float|int', functionA($intFloat));
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
 * @param \DateTimeInterface $dateTimeInterface
 */
function assertTypeTest($dateTimeInterface) {
	assertType('DateTime', functionB(new \DateTime()));
	assertType('DateTimeImmutable', functionB(new \DateTimeImmutable()));
	assertType('DateTimeInterface', functionB($dateTimeInterface));
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

/**
 * @param array<int, string> $arrayOfString
 */
function functionCTest($arrayOfString) {
	assertType('array<int, string>', functionC($arrayOfString));
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
 * @param int $int
 * @param float $float
 * @param int|float $intFloat
 */
function functionDTest($int, $float, $intFloat) {
	assertType('int', functionD($int, $int));
	assertType('float|int', functionD($int, $float));
	assertType('DateTime|int', functionD($int, new \DateTime()));
	assertType('DateTime|float|int', functionD($intFloat, new \DateTime()));
	assertType('array()|DateTime', functionD([], new \DateTime()));
}

/**
 * @template T
 * @param array<\DateTime|array<T>> $a
 * @return T
 */
function functionE($a) {
	if (isset($a[0])) {
		$b = $a[0];
		if (!$b instanceof \DateTime && isset($b[0])) {
			assertType('T', $b[0]);
			return $b[0];
		}
	}

	throw new \Exception();
}

/**
 * @param int $int
 */
function functionETest($int) {
	assertType('int', functionE([[$int]]));
}

/**
 * @template A
 * @template B
 *
 * @param array<A> $a
 * @param callable(A):B $b
 *
 * @return array<B>
 */
function functionF($a, $b) {
	$result = [];
	assertType('array<A>', $a);
	assertType('callable(A): B', $b);
	foreach ($a as $k => $v) {
		assertType('A', $v);
		$newV = $b($v);
		assertType('B', $newV);
		$result[$k] = $newV;
	}
	return $result;
}

/**
 * @param array<int> $arrayOfInt
 * @param null|(callable(int):string) $callableOrNull
 */
function functionFTest($arrayOfInt, $callableOrNull) {
	assertType('array<string>', functionF($arrayOfInt, function (int $a): string {
		return (string) $a;
	}));
	assertType('array<string>', functionF($arrayOfInt, function ($a): string {
		return (string) $a;
	}));
	assertType('array', functionF($arrayOfInt, function ($a) {
		return $a;
	}));
	assertType('array<string>', functionF($arrayOfInt, $callableOrNull));
	assertType('array', functionF($arrayOfInt, null));
	assertType('array', functionF($arrayOfInt, ''));
}

/**
 * @template T
 * @param T $a
 * @return array<T>
 */
function functionG($a) {
	return [$a];
}

/**
 * @param int $int
 */
function functionGTest($int) {
	assertType('array<int>', functionG($int));
}
