<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\TemplateTypeMap;
use PHPStan\Type\Generic\AcceptEqualsStrategy;
use PHPStan\Type\Type;

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

	public static function getInternalReturnType(ParametersAcceptor $parametersAcceptor): Type
	{
		$type = $parametersAcceptor->getReturnType();
		$strategy = new AcceptEqualsStrategy();
		return $type->map(function (Type $type) use ($strategy): Type {
			if ($type instanceof TemplateType) {
				return $type->withAcceptStrategy($strategy);
			}

			return $type;
		});
	}

}
