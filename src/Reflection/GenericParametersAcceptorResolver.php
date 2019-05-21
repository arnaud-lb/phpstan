<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\DummyParameter;

class GenericParametersAcceptorResolver
{

	/**
	 * @param \PhpParser\Node\Arg[] $args
	 */
	public static function resolve(Scope $scope, array $args, ParametersAcceptor $parametersAcceptor): ParametersAcceptor
	{
		$types = TemplateTypeMap::empty();

		foreach ($parametersAcceptor->getParameters() as $n => $param) {
			if (!isset($args[$n])) {
				break;
			}

			$paramType = $param->getType();
			$argType = $scope->getType($args[$n]->value);
			$types = $types->union($paramType->inferTemplateTypes($argType));
		}

		return new FunctionVariant(
			array_map(static function (ParameterReflection $param) use ($types): ParameterReflection {
				return new DummyParameter(
					$param->getName(),
					$param->getType()->resolveTemplateTypes($types),
					$param->isOptional(),
					$param->passedByReference(),
					$param->isVariadic()
				);
			}, $parametersAcceptor->getParameters()),
			$parametersAcceptor->isVariadic(),
			$parametersAcceptor->getReturnType()->resolveTemplateTypes($types)
		);
	}

}
