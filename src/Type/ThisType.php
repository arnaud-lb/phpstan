<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ResolvedPropertyReflection;
use PHPStan\Type\Generic\TemplateTypeHelper;

class ThisType extends StaticType
{

	public function describe(VerbosityLevel $level): string
	{
		return sprintf('$this(%s)', $this->getStaticObjectType()->describe($level));
	}


	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$property = parent::getProperty($propertyName, $scope);

		return new ResolvedPropertyReflection(
			$property,
			$classReflection->getActiveTemplateTypeMap()->map(static function (string $name, Type $type): Type {
				return TemplateTypeHelper::toArgument($type);
			})
		);
	}

}
