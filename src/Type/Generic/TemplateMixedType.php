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

	public function __construct(
		TemplateTypeScope $scope,
		string $name,
		bool $isExplicitMixed = false,
		?Type $subtractedType = null
	)
	{
		parent::__construct($isExplicitMixed, $subtractedType);

		$this->scope = $scope;
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
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

}
