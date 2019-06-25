<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\Tag\DeprecatedTag;
use PHPStan\PhpDoc\Tag\ReturnTag;
use PHPStan\PhpDoc\Tag\ThrowsTag;

class ResolvedPhpDocBlock
{

	/**
	 * The NameScope used while resolving this doc block
	 *
	 * @var NameScope
	 */
	private $nameScope;

	/** @var array<string|int, \PHPStan\PhpDoc\Tag\VarTag> */
	private $varTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\MethodTag> */
	private $methodTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\PropertyTag> */
	private $propertyTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\TemplateTag> */
	private $templateTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\ParamTag> */
	private $paramTags;

	/** @var \PHPStan\PhpDoc\Tag\ReturnTag|null */
	private $returnTag;

	/** @var \PHPStan\PhpDoc\Tag\ThrowsTag|null */
	private $throwsTag;

	/** @var \PHPStan\PhpDoc\Tag\DeprecatedTag|null */
	private $deprecatedTag;

	/** @var bool */
	private $isDeprecated;

	/** @var bool */
	private $isInternal;

	/** @var bool */
	private $isFinal;

	/**
	 * @param NameScope $nameScope
	 * @param array<string|int, \PHPStan\PhpDoc\Tag\VarTag> $varTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\MethodTag> $methodTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\PropertyTag> $propertyTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\TemplateTag> $templateTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\ParamTag> $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 * @param \PHPStan\PhpDoc\Tag\ThrowsTag|null $throwsTags
	 * @param \PHPStan\PhpDoc\Tag\DeprecatedTag|null $deprecatedTag
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 */
	private function __construct(
		NameScope $nameScope,
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $templateTags,
		array $paramTags,
		?ReturnTag $returnTag,
		?ThrowsTag $throwsTags,
		?DeprecatedTag $deprecatedTag,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal
	)
	{
		$this->nameScope = $nameScope;
		$this->varTags = $varTags;
		$this->methodTags = $methodTags;
		$this->propertyTags = $propertyTags;
		$this->templateTags = $templateTags;
		$this->paramTags = $paramTags;
		$this->returnTag = $returnTag;
		$this->throwsTag = $throwsTags;
		$this->deprecatedTag = $deprecatedTag;
		$this->isDeprecated = $isDeprecated;
		$this->isInternal = $isInternal;
		$this->isFinal = $isFinal;
	}

	/**
	 * @param NameScope $nameScope
	 * @param array<string|int, \PHPStan\PhpDoc\Tag\VarTag> $varTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\MethodTag> $methodTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\PropertyTag> $propertyTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\TemplateTag> $templateTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\ParamTag> $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 * @param \PHPStan\PhpDoc\Tag\ThrowsTag|null $throwsTag
	 * @param \PHPStan\PhpDoc\Tag\DeprecatedTag|null $deprecatedTag
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 * @return self
	 */
	public static function create(
		NameScope $nameScope,
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $templateTags,
		array $paramTags,
		?ReturnTag $returnTag,
		?ThrowsTag $throwsTag,
		?DeprecatedTag $deprecatedTag,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal
	): self
	{
		return new self(
			$nameScope,
			$varTags,
			$methodTags,
			$propertyTags,
			$templateTags,
			$paramTags,
			$returnTag,
			$throwsTag,
			$deprecatedTag,
			$isDeprecated,
			$isInternal,
			$isFinal
		);
	}

	public static function createEmpty(): self
	{
		return new self(NameScope::createEmpty(), [], [], [], [], [], null, null, null, false, false, false);
	}

	public function getNameScope(): NameScope
	{
		return $this->nameScope;
	}

	/**
	 * @return array<string|int, \PHPStan\PhpDoc\Tag\VarTag>
	 */
	public function getVarTags(): array
	{
		return $this->varTags;
	}

	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\MethodTag>
	 */
	public function getMethodTags(): array
	{
		return $this->methodTags;
	}

	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\PropertyTag>
	 */
	public function getPropertyTags(): array
	{
		return $this->propertyTags;
	}

	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\TemplateTag>
	 */
	public function getTemplateTags(): array
	{
		return $this->templateTags;
	}

	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\ParamTag>
	 */
	public function getParamTags(): array
	{
		return $this->paramTags;
	}

	public function getReturnTag(): ?\PHPStan\PhpDoc\Tag\ReturnTag
	{
		return $this->returnTag;
	}

	public function getThrowsTag(): ?\PHPStan\PhpDoc\Tag\ThrowsTag
	{
		return $this->throwsTag;
	}

	public function getDeprecatedTag(): ?\PHPStan\PhpDoc\Tag\DeprecatedTag
	{
		return $this->deprecatedTag;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

	public function isInternal(): bool
	{
		return $this->isInternal;
	}

	public function isFinal(): bool
	{
		return $this->isFinal;
	}

	/**
	 * @param mixed[] $properties
	 * @return self
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['nameScope'],
			$properties['varTags'],
			$properties['methodTags'],
			$properties['propertyTags'],
			$properties['templateTags'],
			$properties['paramTags'],
			$properties['returnTag'],
			$properties['throwsTag'],
			$properties['deprecatedTag'],
			$properties['isDeprecated'],
			$properties['isInternal'],
			$properties['isFinal']
		);
	}

}
