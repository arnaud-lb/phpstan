<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;

class GenericHelper
{

	public static function resolveTemplateTypes(Type $type, TemplateTypeMap $types): Type
	{
		return $type->map(static function (Type $type) use ($types): Type {
			if ($type instanceof TemplateType && !$type->isArgument()) {
				$newType = $types->getType($type->getName());

				if ($newType === null) {
					return new ErrorType();
				}

				return $newType;
			}

			return $type;
		});
	}

	public static function toArgument(Type $type): Type
	{
		return $type->map(static function (Type $type): Type {
			if ($type instanceof TemplateType) {
				return $type->toArgument();
			}

			return $type;
		});
	}

}
