<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface AcceptStrategy
{

	public function accepts(TemplateType $left, Type $right, bool $strictTypes): TrinaryLogic;

	public function isSuperTypeOf(TemplateType $left, Type $right): TrinaryLogic;

	public function isSubTypeOf(TemplateType $left, Type $right): TrinaryLogic;

}
