<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Reflection\TemplateTypeMap;
use PHPStan\Type\Type;

trait NonGenericTypeTrait
{

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		return TemplateTypeMap::empty();
	}

	public function resolveTemplateTypes(TemplateTypeMap $types): Type
	{
		return $this;
	}

}
