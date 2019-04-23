<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ClassNameType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;

class IsSubclassOfFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, TypeSpecifierContext $context): bool
	{
		return strtolower($functionReflection->getName()) === 'is_subclass_of'
			&& count($node->args) >= 2
			&& !$context->null();
	}

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		$objectType = $scope->getType($node->args[0]->value);
		$stringType = new StringType();
		$classType = $scope->getType($node->args[1]->value);

		if ($classType instanceof ConstantStringType && $classType->getValue() !== '') {
			$type = TypeCombinator::union(
				new ObjectType($classType->getValue()),
				new ClassNameType($classType->getValue())
			);
		} elseif ($objectType instanceof UnionType) {
			$type = TypeCombinator::union(...array_filter(
				$objectType->getTypes(),
				static function (Type $type) {
					return $type instanceof ObjectWithoutClassType
						|| $type instanceof TypeWithClassName
						|| $type instanceof StringType
						|| $type instanceof ClassNameType;
				}
			));
		} else {
			$type = TypeCombinator::union(
				new ObjectWithoutClassType(),
				new StringType()
			);
		}

		$types = $this->typeSpecifier->create($node->args[0]->value, $type, $context);

		if (!$objectType->isSuperTypeOf(new StringType())->no()
			&& (!isset($node->args[2])
				|| $scope->getType($node->args[2]->value)->equals(new ConstantBooleanType(true)))
		) {
			if ($objectType instanceof ConstantStringType) {
				$type = $objectType;
			} elseif ($classType instanceof ConstantStringType) {
				$type = new ClassNameType($classType->getValue());
			} else {
				$type = $stringType;
			}

			$stringTypes = $this->typeSpecifier->create(
				$node->args[0]->value,
				$type,
				$context
			);

			$types = $types->intersectWith($stringTypes);
		}

		return $types;
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}
