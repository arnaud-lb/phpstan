<?php declare(strict_types = 1);

namespace PHPStan\Type;

class AcceptCovariantStrategy implements AcceptStrategy
{

	public function accepts(TemplateType $left, Type $right, bool $strictTypes): TrinaryLogic
	{
		return $left->getType()->accepts($right, $strictTypes);
	}

	public function isSuperTypeOf(TemplateType $left, Type $right): TrinaryLogic
	{
		return $left->getType()->isSuperTypeOf($right);
	}

	public function isSubTypeOf(TemplateType $left, Type $right): TrinaryLogic
	{
		return $right->isSuperTypeOf($left->getType());
	}

}
