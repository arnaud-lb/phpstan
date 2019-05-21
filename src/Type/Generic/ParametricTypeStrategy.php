<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface ParametricTypeStrategy
{

	/**
	 * @param callable(Type $type, bool $strictTypes): TrinaryLogic $accepts
	 */
	public function accepts(TemplateType $left, Type $right, bool $strictTypes, callable $accepts): TrinaryLogic;

	/**
	 * @param callable(Type $type): TrinaryLogic $isSuperTypeOf
	 */
	public function isSuperTypeOf(TemplateType $left, Type $right, callable $isSuperTypeOf): TrinaryLogic;

	public function isArgument(): bool;

}
