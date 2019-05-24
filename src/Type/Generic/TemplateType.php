<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\Type;

interface TemplateType extends Type
{

	public function getName(): string;

	public function getScope(): TemplateTypeScope;

	public function withAcceptStrategy(AcceptStrategy $strategy): TemplateType;

}
