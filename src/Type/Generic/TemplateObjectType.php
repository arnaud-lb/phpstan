<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Reflection\TemplateTypeMap;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

final class TemplateObjectType extends ObjectType implements TemplateType
{
	/** @var TemplateTypeScope */
	private $scope;

	/** @var string */
	private $name;

	public function __construct(
		TemplateTypeScope $scope,
		string $name,
		string $class,
		?Type $subtractedType = null
	)
	{
		parent::__construct($class, $subtractedType);

		$this->scope = $scope;
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
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
