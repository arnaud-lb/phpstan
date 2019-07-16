<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface ExtendedConstantReflection extends ConstantReflection
{

	public function isDeprecated(): bool;

	public function getDeprecatedDescription(): ?string;

	public function isInternal(): bool;

}
