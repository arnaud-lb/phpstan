<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\TemplateMixedType;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;

class ClassStringTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		$templateType = function (?Type $bound = null): Type {
			return TemplateTypeFactory::create(
				new TemplateTypeScope(null, null),
				'T',
				$bound
			);
		};

		return [
			[
				new ClassStringType(new ObjectType('DateTime')),
				new ClassStringType(new ObjectType('DateTime')),
				TrinaryLogic::createYes(),
			],
			[
				new ClassStringType(new ObjectType('DateTimeInterface')),
				new ClassStringType(new ObjectType('DateTime')),
				TrinaryLogic::createYes(),
			],
			[
				new ClassStringType(new ObjectType('Iterator')),
				new ClassStringType(new ObjectType('DateTime')),
				TrinaryLogic::createMaybe(),
			],
			[
				new ClassStringType(new ObjectType('DateTime')),
				new ClassStringType($templateType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new ClassStringType(new ObjectType('DateTime')),
				new ClassStringType($templateType(new ObjectType('DateTime'))),
				TrinaryLogic::createYes(),
			],
			[
				new ClassStringType(new ObjectType('DateTime')),
				new ConstantStringType('DateTime'),
				TrinaryLogic::createYes(),
			],
			[
				new ClassStringType(new ObjectType('DateTime')),
				new ConstantStringType('DateTime'),
				TrinaryLogic::createYes(),
			],
			[
				new ClassStringType(new ObjectType('Iterator')),
				new ConstantStringType('DateTime'),
				TrinaryLogic::createMaybe(),
			],
			[
				new ClassStringType(new ObjectType('Iterator')),
				new StringType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new ClassStringType(new ObjectType('Iterator')),
				new IntegerType(),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 */
	public function testIsSuperTypeOf(Type $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}


	public function dataInferTemplateTypes()
	{
		$templateType = static function (string $name, ?Type $bound = null): Type {
			return TemplateTypeFactory::create(
				new TemplateTypeScope(null, null),
				$name,
				$bound
			);
		};

		return [
			[
				new ConstantStringType('DateTime'),
				new ClassStringType($templateType('T')),
				['T' => 'DateTime'],
			],
			[
				new StringType(),
				new ClassStringType($templateType('T')),
				[],
			],
			[
				new ClassStringType(new ObjectType('DateTime')),
				new ClassStringType($templateType('T')),
				['T' => 'DateTime'],
			],
			[
				new ClassStringType($templateType('U', new ObjectType('DateTime'))),
				new ClassStringType($templateType('T')),
				['T' => 'U of DateTime'],
			],
			[
				new ClassStringType($templateType('U')),
				new ClassStringType($templateType('T')),
				['T' => 'U'],
			],
			[
				new ClassStringType(new ObjectType('stdClass')),
				new ClassStringType($templateType('T', new ObjectType('DateTime'))),
				[],
			],
			[
				TypeCombinator::union(
					new ConstantStringType('DateTime'),
					new ConstantStringType('stdClass'),
					new NullType()
				),
				new ClassStringType($templateType('T')),
				['T' => 'DateTime|stdClass'],
			],
		];
	}

	/**
	 * @dataProvider dataInferTemplateTypes
	 * @param array<string,string> $expectedTypes
	 */
	public function testResolveTemplateTypes(Type $received, Type $template, array $expectedTypes): void
	{
		$result = $template->inferTemplateTypes($received);

		$this->assertSame(
			$expectedTypes,
			array_map(static function (Type $type): string {
				return $type->describe(VerbosityLevel::precise());
			}, $result->getTypes())
		);
	}

}
