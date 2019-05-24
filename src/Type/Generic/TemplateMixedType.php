<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Reflection\TemplateTypeMap;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

final class TemplateMixedType extends MixedType implements TemplateType
{

	/** @var TemplateTypeScope */
	private $scope;

	/** @var string */
	private $name;

	/** @var AcceptStrategy */
	private $acceptStrategy;

	public function __construct(
		TemplateTypeScope $scope,
		string $name,
		bool $isExplicitMixed = false,
		?Type $subtractedType = null,
		AcceptStrategy $acceptStrategy
	)
	{
		parent::__construct($isExplicitMixed, $subtractedType);

		$this->scope = $scope;
		$this->name = $name;
		$this->acceptStrategy = $acceptStrategy;
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
		return $this->name;
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $type->scope->equals($this->scope)
			&& $type->name === $this->name;
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		return $this->acceptStrategy->accepts(
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
		return $this->acceptStrategy->isSuperTypeOf(
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

	public function withAcceptStrategy(AcceptStrategy $acceptStrategy): TemplateType
	{
		return new self(
			$this->scope,
			$this->name,
			$this->isExplicitMixed(),
			$this->getSubtractedType(),
			$acceptStrategy
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
			$properties['isExplicitMixed'],
			$properties['subtractedType'],
			$properties['acceptStrategy']
		);
	}

}
