<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TypeTraverser
{
	public static function traverse(Type $type): Type
	{
		$newType = ($this->visitor)->visit($type);
		if ($newType !== $type) {
			return $newType;
		}

		return $type->traverse(static function (Type $type): Type {
			return self::traverse($type);
		});
	}
}
