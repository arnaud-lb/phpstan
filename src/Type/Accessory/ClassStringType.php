<?php declare(strict_types=1);

namespace PHPStan\Type\Accessory;

use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\AccessoryType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantScalarToBooleanTrait;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\JustNullableTypeTrait;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Traits\MaybeCallableTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

final class ClassStringType implements AccessoryType, CompoundType
{
	use JustNullableTypeTrait;
	use MaybeCallableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;
	use UndecidedBooleanTypeTrait;

	/** @var ObjectType|TemplateType */
	private $type;

	public static function create(Type $type): ?Type
	{
		if (!$type instanceof ObjectType && !$type instanceof TemplateType) {
			return null;
		}

		return new self($type);
	}

	public function __construct(Type $type)
	{
		$this->type = $type;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf("class-string<%s>", $this->type->describe($level));
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($this->equals($type)) {
			return TrinaryLogic::createYes();
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		if ($type instanceof ConstantStringType) {
			return $this->type->isSuperTypeOf(new ObjectType($type->getValue()))
				->and(TrinaryLogic::createMaybe());
		}

		if ($type instanceof self) {
			return $this->type->isSuperTypeOf($type->type);
		}

		return (new StringType())->isSuperTypeOf($type)
		   ->and(TrinaryLogic::createMaybe());
	}

	public function isSubTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof UnionType || $type instanceof IntersectionType) {
			return $type->isSuperTypeOf($this);
		}

		if ($type instanceof ConstantStringType) {
			return (new ObjectType($type->getValue()))->isSuperTypeOf($this->type)
				->and(TrinaryLogic::createMaybe());
		}

		if ($type instanceof self) {
			return $type->type->isSuperTypeOf($this->type);
		}

		return $type->isSuperTypeOf(new StringType())
			->and(TrinaryLogic::createMaybe());
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $this->type->equals($type->type);
	}

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		if ($receivedType instanceof UnionType || $receivedType instanceof IntersectionType) {
			return $receivedType->inferTemplateTypesOn($this);
		}

		if ($receivedType instanceof ConstantStringType) {
			$objectType = new ObjectType($receivedType->getValue());
			return $this->type->inferTemplateTypes($objectType);
		}

		if ($receivedType instanceof self) {
			return $this->type->inferTemplateTypes($receivedType->type);
		}

		return TemplateTypeMap::empty();
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['type']);
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		return (new IntegerType())->isSuperTypeOf($offsetType)->and(TrinaryLogic::createMaybe());
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		if ($this->hasOffsetValueType($offsetType)->no()) {
			return new ErrorType();
		}

		return new StringType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		if ($offsetType === null) {
			return new ErrorType();
		}

		$valueStringType = $valueType->toString();
		if ($valueStringType instanceof ErrorType) {
			return new ErrorType();
		}

		if ((new IntegerType())->isSuperTypeOf($offsetType)->yes()) {
			return new StringType();
		}

		return new ErrorType();
	}

	public function toNumber(): Type
	{
		return new ErrorType();
	}

	public function toInteger(): Type
	{
		return new IntegerType();
	}

	public function toFloat(): Type
	{
		return new FloatType();
	}

	public function toString(): Type
	{
		return $this;
	}

	public function toArray(): Type
	{
		return new ConstantArrayType(
			[new ConstantIntegerType(0)],
			[$this],
			1
		);
	}

	private function isSubclassOf(string $subject, string $parent): TrinaryLogic
	{
		if ($subject === $parent) {
			return TrinaryLogic::createYes();
		}

		$subjectObject = new ObjectType($subject);
		$parentObject = new ObjectType($parent);

		return $parentObject->isSuperTypeOf($subjectObject);
	}

	public function getClassName(): ?string
	{
		if ($this->type instanceof ObjectType) {
			return $this->type->getClassName();
		}

		if ($this->type instanceof TemplateType) {
			$bound = $this->type->getBound();
			if ($bound instanceof ObjectType) {
				return $bound->getClassName();
			}
		}

		return null;
	}

}
