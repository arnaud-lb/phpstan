<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

final class TemplateObjectType extends ObjectType implements TemplateType
{

	/** @var TemplateTypeScope */
	private $scope;

	/** @var string */
	private $name;

	/** @var ParametricTypeStrategy */
	private $strategy;

	public function __construct(
		TemplateTypeScope $scope,
		string $name,
		string $class,
		?Type $subtractedType = null,
		?ParametricTypeStrategy $parametricTypeStrategy = null
	)
	{
		parent::__construct($class, $subtractedType);

		$this->scope = $scope;
		$this->name = $name;
		$this->strategy = $parametricTypeStrategy ?? new ParametricTypeParameterStrategy();
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getScope(): TemplateTypeScope
	{
		return $this->scope;
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf(
			'%s of %s',
			$this->name,
			parent::describe($level)
		);
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $type->scope->equals($this->scope)
			&& $type->name === $this->name;
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		return $this->strategy->accepts(
			$this,
			$type,
			$strictTypes,
			function (Type $type, bool $strictTypes): TrinaryLogic {
				return parent::accepts($type, $strictTypes);
			}
		);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		return $this->strategy->isSuperTypeOf(
			$this,
			$type,
			function (Type $type): TrinaryLogic {
				return parent::isSuperTypeOf($type);
			}
		);
	}

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		if ($receivedType instanceof UnionType || $receivedType instanceof IntersectionType) {
			return $receivedType->inferTemplateTypesOn($this);
		}

		if (!$this->accepts($receivedType, true)->yes()) {
			$receivedType = new NeverType();
		}

		return new TemplateTypeMap([
			$this->name => $receivedType,
		]);
	}

	public function isArgument(): bool
	{
		return $this->strategy->isArgument();
	}

	public function toArgument(): TemplateType
	{
		return new self(
			$this->scope,
			$this->name,
			$this->getClassName(),
			$this->getSubtractedType(),
			new ParametricTypeArgumentStrategy()
		);
	}

	public function subtract(Type $type): Type
	{
		if ($this->getSubtractedType() !== null) {
			$type = TypeCombinator::union($this->getSubtractedType(), $type);
		}

		return new self(
			$this->scope,
			$this->name,
			$this->getClassName(),
			$type,
			$this->strategy
		);
	}

	public function getTypeWithoutSubtractedType(): Type
	{
		return new self(
			$this->scope,
			$this->name,
			$this->getClassName(),
			null,
			$this->strategy
		);
	}

	public function changeSubtractedType(?Type $subtractedType): Type
	{
		return new self(
			$this->scope,
			$this->name,
			$this->getClassName(),
			$subtractedType,
			$this->strategy
		);
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['scope'],
			$properties['name'],
			$properties['className'],
			$properties['subtractedType'],
			$properties['strategy']
		);
	}

}
