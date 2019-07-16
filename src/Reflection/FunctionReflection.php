<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface FunctionReflection
{

	public function getName(): string;

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array;

	public function isDeprecated(): bool;

	public function getDeprecatedDescription(): ?string;

	public function isInternal(): bool;

	public function isFinal(): bool;

	public function getThrowType(): ?Type;

}
