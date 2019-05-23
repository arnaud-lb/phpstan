<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

class TemplateTypeScope
{

	/** @var string|null */
	private $className;

	/** @var string|null */
	private $functionName;

	public function __construct(?string $className, ?string $functionName)
	{
		$this->className = $className;
		$this->functionName = $functionName;
	}

	public function equals(self $other): ?string
	{
		return $this->className === $other->className
			&& $this->functionName === $other->functionName;
	}

}
