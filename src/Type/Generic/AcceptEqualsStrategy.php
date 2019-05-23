<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class AcceptEqualsStrategy implements AcceptStrategy
{

	public function accepts(TemplateType $left, Type $right, bool $strictTypes): TrinaryLogic
	{
		if (!$right instanceof TemplateType) {
			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createFromBoolean(
			$left->getScope()->equals($right->getScope())
			&& $left->getName() === $right->getName()
		);
	}

	public function isSuperTypeOf(TemplateType $left, Type $right): TrinaryLogic
	{
		return $this->accepts($left, $right, true);
	}

	public function isSubTypeOf(TemplateType $left, Type $right): TrinaryLogic
	{
		return $this->accepts($left, $right, true);
	}

}
