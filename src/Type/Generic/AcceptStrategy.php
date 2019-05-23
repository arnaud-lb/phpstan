<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface AcceptStrategy
{

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic;

	public function isSuperTypeOf(Type $type): TrinaryLogic;

}
