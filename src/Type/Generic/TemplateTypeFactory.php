<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\PhpDoc\Tag\TemplateTag;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class TemplateTypeFactory
{

	public static function create(TemplateTypeScope $scope, string $name, Type $bound): TemplateType
	{
		if ($bound instanceof ObjectType) {
			return new TemplateObjectType($scope, $name, $bound->getClassName());
		}

		if ($bound instanceof MixedType) {
			return new TemplateMixedType($scope, $name);
		}

		return new ErrorType();
	}

	public static function fromTemplateTag(TemplateTag $tag): TemplateType
	{
		return self::create($tag->getName(), $tag->getBound());
	}

}
