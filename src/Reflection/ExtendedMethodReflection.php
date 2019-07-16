<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface ExtendedMethodReflection extends MethodReflection
{

	public function isFinal(): bool;

	public function getThrowType(): ?Type;

	public function isDeprecated(): bool;

	public function getDeprecatedDescription(): ?string;

	public function isInternal(): bool;

}
