<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\GenericHelper;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;

class ParametersAcceptorSelector
{

	/**
	 * @param ParametersAcceptor[] $parametersAcceptors
	 * @return ParametersAcceptor
	 */
	public static function selectSingle(
		array $parametersAcceptors
	): ParametersAcceptor
	{
		if (count($parametersAcceptors) !== 1) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $parametersAcceptors[0];
	}

	/**
	 * Returns argument types (types as seen in the function body)
	 *
	 * @param ParametersAcceptor[] $parametersAcceptors
	 */
	public static function selectArguments(
		array $parametersAcceptors
	): ParametersAcceptor
	{
		if (count($parametersAcceptors) !== 1) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$parametersAcceptor = $parametersAcceptors[0];

		return new FunctionVariant(
			TemplateTypeMap::empty(),
			array_map(static function (ParameterReflection $param): ParameterReflection {
				return new DummyParameter(
					$param->getName(),
					GenericHelper::toArgument($param->getType()),
					$param->isOptional(),
					$param->passedByReference(),
					$param->isVariadic()
				);
			}, $parametersAcceptor->getParameters()),
			$parametersAcceptor->isVariadic(),
			GenericHelper::toArgument($parametersAcceptor->getReturnType())
		);
	}

	/**
	 * @param Scope $scope
	 * @param \PhpParser\Node\Arg[] $args
	 * @param ParametersAcceptor[] $parametersAcceptors
	 * @return ParametersAcceptor
	 */
	public static function selectFromArgs(
		Scope $scope,
		array $args,
		array $parametersAcceptors
	): ParametersAcceptor
	{
		$types = [];
		$unpack = false;
		foreach ($args as $arg) {
			$type = $scope->getType($arg->value);
			if ($arg->unpack) {
				$unpack = true;
				$types[] = $type->getIterableValueType();
			} else {
				$types[] = $type;
			}
		}

		return self::selectFromTypes($types, $parametersAcceptors, $unpack);
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @param ParametersAcceptor[] $parametersAcceptors
	 * @param bool $unpack
	 * @return ParametersAcceptor
	 */
	public static function selectFromTypes(
		array $types,
		array $parametersAcceptors,
		bool $unpack
	): ParametersAcceptor
	{
		if (count($parametersAcceptors) === 1) {
			return self::resolveTemplateTypes($types, $parametersAcceptors[0]);
		}

		if (count($parametersAcceptors) === 0) {
			throw new \PHPStan\ShouldNotHappenException(
				'getVariants() must return at least one variant.'
			);
		}

		$typesCount = count($types);
		$acceptableAcceptors = [];

		foreach ($parametersAcceptors as $parametersAcceptor) {
			if ($unpack) {
				$acceptableAcceptors[] = $parametersAcceptor;
				continue;
			}

			$functionParametersMinCount = 0;
			$functionParametersMaxCount = 0;
			foreach ($parametersAcceptor->getParameters() as $parameter) {
				if (!$parameter->isOptional()) {
					$functionParametersMinCount++;
				}

				$functionParametersMaxCount++;
			}

			if ($typesCount < $functionParametersMinCount) {
				continue;
			}

			if (
				!$parametersAcceptor->isVariadic()
				&& $typesCount > $functionParametersMaxCount
			) {
				continue;
			}

			$acceptableAcceptors[] = $parametersAcceptor;
		}

		if (count($acceptableAcceptors) === 0) {
			return self::resolveTemplateTypes($types, self::combineAcceptors($parametersAcceptors));
		}

		if (count($acceptableAcceptors) === 1) {
			return self::resolveTemplateTypes($types, $acceptableAcceptors[0]);
		}

		$winningAcceptors = [];
		$winningCertainty = null;
		foreach ($acceptableAcceptors as $acceptableAcceptor) {
			$isSuperType = TrinaryLogic::createYes();
			$acceptableAcceptor = self::resolveTemplateTypes($types, $acceptableAcceptor);
			foreach ($acceptableAcceptor->getParameters() as $i => $parameter) {
				if (!isset($types[$i])) {
					if (!$unpack || count($types) <= 0) {
						break;
					}

					$type = $types[count($types) - 1];
				} else {
					$type = $types[$i];
				}

				if ($parameter->getType() instanceof MixedType) {
					$isSuperType = $isSuperType->and(TrinaryLogic::createMaybe());
				} else {
					$isSuperType = $isSuperType->and($parameter->getType()->isSuperTypeOf($type));
				}
			}

			if ($isSuperType->no()) {
				continue;
			}

			if ($winningCertainty === null) {
				$winningAcceptors[] = $acceptableAcceptor;
				$winningCertainty = $isSuperType;
			} else {
				$comparison = $winningCertainty->compareTo($isSuperType);
				if ($comparison === $isSuperType) {
					$winningAcceptors = [$acceptableAcceptor];
					$winningCertainty = $isSuperType;
				} elseif ($comparison === null) {
					$winningAcceptors[] = $acceptableAcceptor;
				}
			}
		}

		if (count($winningAcceptors) === 0) {
			return self::resolveTemplateTypes($types, self::combineAcceptors($acceptableAcceptors));
		}

		return self::combineAcceptors($winningAcceptors);
	}

	/**
	 * @param ParametersAcceptor[] $acceptors
	 * @return ParametersAcceptor
	 */
	public static function combineAcceptors(array $acceptors): ParametersAcceptor
	{
		if (count($acceptors) === 0) {
			throw new \PHPStan\ShouldNotHappenException(
				'getVariants() must return at least one variant.'
			);
		}
		if (count($acceptors) === 1) {
			return $acceptors[0];
		}

		$minimumNumberOfParameters = null;
		foreach ($acceptors as $acceptor) {
			$acceptorParametersMinCount = 0;
			foreach ($acceptor->getParameters() as $parameter) {
				if ($parameter->isOptional()) {
					continue;
				}

				$acceptorParametersMinCount++;
			}

			if ($minimumNumberOfParameters !== null && $minimumNumberOfParameters <= $acceptorParametersMinCount) {
				continue;
			}

			$minimumNumberOfParameters = $acceptorParametersMinCount;
		}

		$parameters = [];
		$isVariadic = false;
		$returnType = null;

		foreach ($acceptors as $acceptor) {
			if ($returnType === null) {
				$returnType = $acceptor->getReturnType();
			} else {
				$returnType = TypeCombinator::union($returnType, $acceptor->getReturnType());
			}
			$isVariadic = $isVariadic || $acceptor->isVariadic();

			foreach ($acceptor->getParameters() as $i => $parameter) {
				if (!isset($parameters[$i])) {
					$parameters[$i] = new NativeParameterReflection(
						$parameter->getName(),
						$i + 1 > $minimumNumberOfParameters,
						$parameter->getType(),
						$parameter->passedByReference(),
						$parameter->isVariadic()
					);
					continue;
				}

				$isVariadic = $parameters[$i]->isVariadic() || $parameter->isVariadic();

				$parameters[$i] = new NativeParameterReflection(
					$parameters[$i]->getName() !== $parameter->getName() ? sprintf('%s|%s', $parameters[$i]->getName(), $parameter->getName()) : $parameter->getName(),
					$i + 1 > $minimumNumberOfParameters,
					TypeCombinator::union($parameters[$i]->getType(), $parameter->getType()),
					$parameters[$i]->passedByReference()->combine($parameter->passedByReference()),
					$isVariadic
				);

				if ($isVariadic) {
					$parameters = array_slice($parameters, 0, $i + 1);
					break;
				}
			}
		}

		return new FunctionVariant(TemplateTypeMap::empty(), $parameters, $isVariadic, $returnType);
	}

	/**
	 * @param \PHPStan\Type\Type[] $argTypes
	 */
	private static function resolveTemplateTypes(array $argTypes, ParametersAcceptor $parametersAcceptor): ParametersAcceptor
	{
		$typeMap = TemplateTypeMap::empty();

		foreach ($parametersAcceptor->getParameters() as $n => $param) {
			if (!isset($argTypes[$n])) {
				break;
			}

			$paramType = $param->getType();
			$typeMap = $typeMap->union($paramType->inferTemplateTypes($argTypes[$n]));
		}

		return new FunctionVariant(
			$typeMap,
			array_map(static function (ParameterReflection $param) use ($typeMap): ParameterReflection {
				return new DummyParameter(
					$param->getName(),
					GenericHelper::resolveTemplateTypes($param->getType(), $typeMap),
					$param->isOptional(),
					$param->passedByReference(),
					$param->isVariadic()
				);
			}, $parametersAcceptor->getParameters()),
			$parametersAcceptor->isVariadic(),
			GenericHelper::resolveTemplateTypes($parametersAcceptor->getReturnType(), $typeMap)
		);
	}

}
