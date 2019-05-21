<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\PhpDoc\Tag\TemplateTag;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class TemplateTypeFactory
{

	public static function create(string $name, Type $bound): TemplateType
	{
		if ($bound instanceof ObjectType) {
			return new TemplateObjectType($name, $bound->getClassName());
		}

		if ($bound instanceof MixedType) {
			return new TemplateMixedType($name);
		}

		// TODO; better exception
		throw new \Exception(sprintf(
			'Unsupported @template base type: %s',
			$bound->describe(VerbosityLevel::typeOnly())
		));
	}

	public static function fromTemplateTag(TemplateTag $tag): TemplateType
	{
		return self::create($tag->getName(), $tag->getBound());
	}

}
