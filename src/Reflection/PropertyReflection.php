<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface PropertyReflection extends ClassMemberReflection
{

	public function getType(): Type;

	public function getWritableType(): Type;

	public function canChangeTypeAfterAssignment(): bool;

	public function isReadable(): bool;

	public function isWritable(): bool;

	public function getExtendedPropertyReflection(): ?ExtendedPropertyReflection;

}
