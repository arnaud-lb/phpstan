<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\Accessory\HasPropertyType;
use PHPStan\Type\ClassNameType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\StringType;

class ClassNameTypeTest extends \PHPStan\Testing\TestCase
{
	public function test()
	{
		$this->assertTrue(
			(new ClassNameType(ConstantStringType::class))
				->isSubTypeOf(new ConstantStringType(StringType::class))
				->yes()
		);

		$this->assertTrue(
			(new ClassNameType(StringType::class))
				->isSuperTypeOf(new ConstantStringType(ConstantStringType::class))
				->yes()
		);

		$this->assertTrue(
			(new ClassNameType(ConstantStringType::class))
				->isSubTypeOf(new ConstantStringType(StringType::class))
				->yes()
		);

		$type = TypeCombinator::union(
			new ObjectType(ConstantStringType::class),
			new ClassNameType(ConstantStringType::class)
		);

		$type = TypeCombinator::intersect(
			new StringType(),
			$type
		);

		var_dump($type);
	}
}
