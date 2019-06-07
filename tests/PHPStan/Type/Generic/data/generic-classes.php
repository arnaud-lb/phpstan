<?php declare(strict_types=1);

namespace PHPStan\Type\Test\A;

/** @template T */
class A {}

/** @extends A<\DateTime> */
class AOfDateTime extends A {}

/**
 * @template U
 * @extends A<U>
 */
class SubA extends A {}

namespace PHPStan\Type\Test\B;

/** @template T */
interface I {}

/**
 * @template T
 * @implements I<T>
 */
class IImpl implements I {}
