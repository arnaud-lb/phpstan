<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface AcceptStrategy
{

	/**
	 * @param callable(Type $type, bool $strictTypes): TrinaryLogic $accepts
	 */
	public function accepts(TemplateType $left, Type $right, bool $strictTypes, callable $accepts): TrinaryLogic;

	/**
	 * @param callable(Type $type): TrinaryLogic $isSuperTypeOf
	 */
	public function isSuperTypeOf(TemplateType $left, Type $right, callable $isSuperTypeOf): TrinaryLogic;

}
