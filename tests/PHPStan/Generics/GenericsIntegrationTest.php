<?php declare(strict_types = 1);

namespace PHPStan\Generics;

use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\File\RelativePathHelper;
use PHPStan\Parser\DirectParser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Rules\AlwaysFailRule;
use PHPStan\Rules\Registry;
use PHPStan\Type\FileTypeMapper;

class GenericsIntegrationTest extends \PHPStan\Testing\LevelsTestCase
{
	/**
	 * @return array<string,array>
	 */
	public function provideGenerics(): array
	{
		$topics = [
			'functions',
			'invalidBound',
			'invalidReturn',
		];

		$topics = array_combine($topics, $topics);
		$topics = array_map(function (string $topic): array {
			$pathPrefix = sprintf('%s/data/%s', __DIR__, $topic);

			require_once sprintf('%s.php', $pathPrefix);

			$errorsPath = sprintf('%s.errors.json', $pathPrefix);
			if (file_exists($errorsPath)) {
				$errors = json_decode(file_get_contents($errorsPath));
			} else {
				$errors = [];
			}

			return [
				$errors,
			];
		}, $topics);
	}

	public function testGenerics(array $errors): void
	{
	}
}
