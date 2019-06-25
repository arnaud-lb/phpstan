<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\PhpDoc\Tag\TemplateTag;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class TemplateTypeMap
{

	/** @var array<string,\PHPStan\Type\Type> */
	private $types;

	/** @param array<string,\PHPStan\Type\Type> $types */
	public function __construct(array $types)
	{
		$this->types = $types;
	}

	/**
	 * @param TemplateTag[] $tags
	 */
	public static function createFromTemplateTags(TemplateTypeScope $scope, array $tags): TemplateTypeMap
	{
		return new self(array_map(function (TemplateTag $tag) use ($scope): Type {
			return TemplateTypeFactory::fromTemplateTag($scope, $tag);
		}, $tags));
	}

	public static function createEmpty(): self
	{
		return new self([]);
	}

	/** @return array<string,\PHPStan\Type\Type> */
	public function getTypes(): array
	{
		return $this->types;
	}

	public function getType(string $name): ?Type
	{
		return $this->types[$name] ?? null;
	}

	public function union(self $other): self
	{
		$result = $this->types;

		foreach ($other->types as $name => $type) {
			if (isset($result[$name])) {
				$result[$name] = TypeCombinator::union($result[$name], $type);
			} else {
				$result[$name] = $type;
			}
		}

		return new self($result);
	}

	public function intersect(self $other): self
	{
		$result = $this->types;

		foreach ($other->types as $name => $type) {
			if (isset($result[$name])) {
				$result[$name] = TypeCombinator::intersect($result[$name], $type);
			} else {
				$result[$name] = $type;
			}
		}

		return new self($result);
	}

	/**
	 * @param mixed[] $properties
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['types']
		);
	}

}
