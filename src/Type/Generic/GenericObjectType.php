<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ResolvedMethodReflection;
use PHPStan\Reflection\ResolvedPropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class GenericObjectType extends ObjectType
{

	/** @var Type[] */
	private $types;

	/**
	 * @param Type[] $types
	 */
	public function __construct(
		string $mainType,
		array $types,
		?Type $subtractedType = null
	)
	{
		parent::__construct($mainType, $subtractedType);
		$this->types = $types;
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf(
			'%s<%s>',
			parent::describe($level),
			implode(', ', array_map(static function (Type $type) use ($level): string {
				return $type->describe($level);
			}, $this->types))
		);
	}

	/** @return Type[] */
	public function getTypes(): array
	{
		return $this->types;
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return $this->isSuperTypeOf($type);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		$nakedSuperTypeOf = parent::isSuperTypeOf($type);
		if ($nakedSuperTypeOf->no()) {
			return $nakedSuperTypeOf;
		}

		if (!$type instanceof ObjectType) {
			return $nakedSuperTypeOf;
		}

		$ancestor = $type->getAncestorWithClassName($this->getClassName());
		if ($ancestor === null || !$ancestor instanceof self) {
			return $nakedSuperTypeOf->and(TrinaryLogic::createMaybe());
		}

		if (count($this->types) !== count($ancestor->types)) {
			return TrinaryLogic::createNo();
		}

		foreach ($this->types as $i => $t) {
			if (!isset($ancestor->types[$i])) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if (!$t->equals($ancestor->types[$i])) {
				return TrinaryLogic::createNo();
			}
		}

		return $nakedSuperTypeOf;
	}

	public function getClassReflection(): ?ClassReflection
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->getClassName())) {
			return null;
		}

		return $broker->getClass($this->getClassName())->withTypes($this->types);
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		$reflection = parent::getProperty($propertyName, $scope);

		return new ResolvedPropertyReflection(
			$reflection,
			$this->getClassReflection()->getActiveTemplateTypeMap()
		);
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		$reflection = parent::getMethod($methodName, $scope);

		return new ResolvedMethodReflection(
			$reflection,
			$this->getClassReflection()->getActiveTemplateTypeMap()
		);
	}

	public function traverse(callable $cb): Type
	{
		$subtractedType = $this->getSubtractedType() !== null ? $cb($this->getSubtractedType()) : null;

		$typesChanged = false;
		$types = [];
		foreach ($this->types as $type) {
			$newType = $cb($type);
			if ($newType === $type) {
				continue;
			}

			$typesChanged = true;
			$types[] = $newType;
		}

		if ($subtractedType !== $this->getSubtractedType() || $typesChanged) {
			return new static(
				$this->getClassName(),
				$types,
				$subtractedType
			);
		}

		return $this;
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['className'],
			$properties['types'],
			$properties['subtractedType'] ?? null
		);
	}

}
