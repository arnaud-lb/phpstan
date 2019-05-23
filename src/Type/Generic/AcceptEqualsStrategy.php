<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class AcceptEqualsStrategy implements AcceptStrategy
{

	public function accepts(Type $self, Type $other, bool $strictTypes): TrinaryLogic
	{
		return $self->accepts($other);
	}

	public function isSuperTypeOf(Type $self, Type $other): TrinaryLogic
	{
		return
	}

}
