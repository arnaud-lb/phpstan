<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\Type;

interface TypeParameterReflection
{

	public function getName(): string;

	public function getBound(): ?Type;

	public function getTemplateTypeScope(): TemplateTypeScope;

}
