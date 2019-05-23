<?php declare(strict_types = 1);

namespace PHPStan\Type;

class GenericHelper
{
	public static function resolveTemplateTypes(Type $type, TemplateTypeMap $types): Type
	{
		return $type->map(function (Type $type) use ($types): Type {
			if ($type instanceof TemplateType) {
				$newType = $types->getType($type->getName());

				if ($newType === null) {
					return new ErrorType();
				}

				return $newType;
			}

			return $type;
		});
	}
}
