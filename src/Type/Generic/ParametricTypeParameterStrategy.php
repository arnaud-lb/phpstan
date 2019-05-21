<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class ParametricTypeParameterStrategy implements ParametricTypeStrategy
{

	public function accepts(TemplateType $left, Type $right, bool $strictTypes, callable $accepts): TrinaryLogic
	{
		return $accepts($right, $strictTypes);
	}

	public function isSuperTypeOf(TemplateType $left, Type $right, callable $isSuperTypeOf): TrinaryLogic
	{
		return $isSuperTypeOf($right);
	}

	public function isArgument(): bool
	{
		return false;
	}

	/**
	 * @param mixed[] $properties
	 */
	public static function __set_state(array $properties): self
	{
		return new self();
	}

}
