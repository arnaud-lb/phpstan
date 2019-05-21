<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class ParametricTypeArgumentStrategy implements ParametricTypeStrategy
{

	public function accepts(TemplateType $left, Type $right, bool $strictTypes, callable $accepts): TrinaryLogic
	{
		if (!$right instanceof TemplateType) {
			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createFromBoolean($left->equals($right));
	}

	public function isSuperTypeOf(TemplateType $left, Type $right, callable $isSuperTypeOf): TrinaryLogic
	{
		return $this->accepts($left, $right, true, static function (Type $type, bool $isSuperTypeOf): TrinaryLogic {
			throw new \PHPStan\ShouldNotHappenException();
		});
	}

	public function isArgument(): bool
	{
		return true;
	}

	/**
	 * @param mixed[] $properties
	 */
	public static function __set_state(array $properties): self
	{
		return new self();
	}

}
