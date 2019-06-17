<?php

namespace PHPStan\Levels\ClassVariable;

use function PHPStan\Testing\assertType;

class C
{
	public static function f(): int {
		return 0;
	}
}

/**
 * @param mixed $a
 */
function testMixed($a) {
	assertType('mixed', new $a());

	if (is_subclass_of($a, 'DateTimeInterface')) {
		assertType('DateTimeInterface|(string&class-string<DateTimeInterface>)', $a);
		assertType('DateTimeInterface', new $a());
	}

	if (is_subclass_of($a, 'DateTimeInterface') || is_subclass_of($a, 'stdClass')) {
		assertType('DateTimeInterface|stdClass|(string&class-string<DateTimeInterface>)|(string&class-string<stdClass>)', $a);
		assertType('DateTimeInterface|stdClass', new $a());
	}

	if (is_subclass_of($a, C::class)) {
		assertType('int', $a::f());
	}
}

/**
 * @param object $a
 */
function testObject($a) {
	assertType('mixed', new $a());

	if (is_subclass_of($a, 'DateTimeInterface')) {
		assertType('DateTimeInterface', $a);
	}
}

/**
 * @param string $a
 */
function testString($a) {
	assertType('mixed', new $a());

	if (is_subclass_of($a, 'DateTimeInterface')) {
		assertType('string&class-string<DateTimeInterface>', $a);
		assertType('DateTimeInterface', new $a());
	}

	if (is_subclass_of($a, C::class)) {
		assertType('int', $a::f());
	}
}

/**
 * @param string|object $a
 */
function testStringObject($a) {
	assertType('mixed', new $a());

	if (is_subclass_of($a, 'DateTimeInterface')) {
		assertType('DateTimeInterface|(string&class-string<DateTimeInterface>)', $a);
		assertType('DateTimeInterface', new $a());
	}

	if (is_subclass_of($a, C::class)) {
		assertType('int', $a::f());
	}
}

/**
 * @param class-string<\DateTimeInterface> $a
 */
function testClassString($a) {
	assertType('DateTimeInterface', new $a());

	if (is_subclass_of($a, 'DateTime')) {
		assertType('string&class-string<DateTime>', $a);
		assertType('DateTime', new $a());
	}
}

/**
 * @template T
 * @param class-string<T> $a
 * @return T
 */
function testClassStringTemplate($a) {
	assertType('T', new $a());

	if (is_subclass_of($a, 'DateTime')) {
		assertType('string&class-string<DateTime>&class-string<T>', $a);
		assertType('DateTime&T', new $a());
		return new $a();
	}

	throw new \Exception();
}
