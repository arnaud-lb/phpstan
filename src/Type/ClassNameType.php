<?php declare(strict_types=1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\AccessoryType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantScalarToBooleanTrait;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\JustNullableTypeTrait;
use PHPStan\Type\StringType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Traits\MaybeCallableTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class ClassNameType implements AccessoryType, CompoundType
{
	use JustNullableTypeTrait;
	use MaybeCallableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;
	use UndecidedBooleanTypeTrait;

	/** @var string */
	private $className;

	public function __construct(string $className)
	{
		$this->className = $className;
	}

	public function getClassName(): string
	{
		return $this->className;
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf("isSubclassOf(%s)", $this->className);
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
			$className = $type->getValue();
		} elseif ($type instanceof self) {
			$className = $type->className;
		} else {
			return (new StringType())->isSuperTypeOf($type)
				->and(TrinaryLogic::createMaybe());
		}

		return $this->isSubclassOf($className, $this->className);
	}

	public function isSubTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof UnionType || $type instanceof IntersectionType) {
			return $type->isSuperTypeOf($this);
		}

		if ($type instanceof ConstantStringType) {
			$className = $type->getValue();
		} elseif ($type instanceof self) {
			$className = $type->className;
		} else {
			return (new StringType())->isSuperTypeOf($type)
				->and(TrinaryLogic::createMaybe());
		}

		return $this->isSubclassOf($this->className, $className);
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $this->className === $type->className;
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['className']);
	}

	public static function getClassNameFromType(Type $type): ?string
	{
		if ($type instanceof ConstantStringType) {
			return $type->getValue();
		}

		if ($type instanceof self) {
			return $type->getClassName();
		}

		return null;
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

		$broker = Broker::getInstance();

		if (!$broker->hasClass($parent) || !$broker->hasClass($subject)) {
			return TrinaryLogic::createMaybe();
		}

		$parentReflection = $broker->getClass($parent);
		$subjectReflection = $broker->getClass($subject);

		if ($parentReflection->getName() === $subjectReflection->getName()) {
			return TrinaryLogic::createYes();
		}

		if ($subjectReflection->isSubclassOf($parent)) {
			return TrinaryLogic::createYes();
		}

		if ($parentReflection->isSubclassOf($subject)) {
			return TrinaryLogic::createMaybe();
		}

		if ($parentReflection->isInterface() && !$subjectReflection->getNativeReflection()->isFinal()) {
			return TrinaryLogic::createMaybe();
		}

		if ($subjectReflection->isInterface() && !$parentReflection->getNativeReflection()->isFinal()) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}
}
