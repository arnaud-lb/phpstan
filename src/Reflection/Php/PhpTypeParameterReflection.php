<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\TypeParameterReflection;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\Type;

class PhpTypeParameterReflection implements TypeParameterReflection
{

	/** @var string */
	private $name;

	/** @var \PHPStan\Type\Type|null */
	private $bound;

	/** @var \PHPStan\Type\Generic\TemplateTypeScope */
	private $scope;

	public function __construct(string $name, ?Type $bound, TemplateTypeScope $scope)
	{
		$this->name = $name;
		$this->bound = $bound;
		$this->scope = $scope;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getBound(): ?Type
	{
		return $this->bound;
	}

	public function getTemplateTypeScope(): TemplateTypeScope
	{
		return $this->scope;
	}

}
