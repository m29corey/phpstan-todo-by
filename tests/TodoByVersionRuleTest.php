<?php

namespace staabm\PHPStanTodoBy\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use staabm\PHPStanTodoBy\TodoByVersionRule;
use staabm\PHPStanTodoBy\utils\ExpiredCommentErrorBuilder;
use staabm\PHPStanTodoBy\utils\GitTagFetcher;
use staabm\PHPStanTodoBy\utils\ReferenceVersionFinder;

/**
 * @extends RuleTestCase<TodoByVersionRule>
 * @internal
 */
final class TodoByVersionRuleTest extends RuleTestCase
{
    private string $referenceVersion;

    protected function getRule(): Rule
    {
        return new TodoByVersionRule(
            true,
            new ReferenceVersionFinder($this->referenceVersion, new GitTagFetcher()),
            new ExpiredCommentErrorBuilder(true),
        );
    }

    /**
     * @param list<array{0: string, 1: int, 2?: string|null}> $errors
     * @dataProvider provideErrors
     */
    public function testRule(string $referenceVersion, array $errors): void
    {
        $this->referenceVersion = $referenceVersion;

        $this->analyse([__DIR__ . '/data/version.php'], $errors);
    }

    /**
     * @return iterable<array{string, list<array{0: string, 1: int, 2?: string|null}>}>
     */
    public static function provideErrors(): iterable
    {
        $tip = "Calculated reference version is '0.1.0.0'.\n\n   See also:\n https://github.com/staabm/phpstan-todo-by#reference-version";

        yield [
            '0.1',
            [
                [
                    'Version requirement <1.0.0 satisfied: This has to be in the first major release.',
                    5,
                    $tip,
                ],
                [
                    'Version requirement <1.0.0 satisfied.',
                    10,
                    $tip,
                ],
                [
                    'Version requirement <1.0 satisfied.',
                    11,
                    $tip,
                ],
            ],
        ];

        $tip = "Calculated reference version is '1.0.0.0'.\n\n   See also:\n https://github.com/staabm/phpstan-todo-by#reference-version";
        yield [
            '1.0',
            [
                [
                    'Version requirement >=1.0 satisfied.',
                    12,
                    $tip,
                ],
            ],
        ];

        $tip = "Calculated reference version is '123.4.0.0'.\n\n   See also:\n https://github.com/staabm/phpstan-todo-by#reference-version";
        yield [
            '123.4',
            [
                [
                    'Version requirement >=1.0 satisfied.',
                    12,
                    $tip,
                ],
            ],
        ];

        $tip = "Calculated reference version is '123.5.0.0'.\n\n   See also:\n https://github.com/staabm/phpstan-todo-by#reference-version";
        yield [
            '123.5',
            [
                [
                    'Version requirement >123.4 satisfied: Must fix this or bump the version.',
                    7,
                    $tip,
                ],
                [
                    'Version requirement >=1.0 satisfied.',
                    12,
                    $tip,
                ],
            ],
        ];
    }

    /**
     * @param list<array{0: string, 1: int, 2?: string|null}> $errors
     * @dataProvider provideSemanticVersions
     */
    public function testSemanticVersions(string $referenceVersion, array $errors): void
    {
        $this->referenceVersion = $referenceVersion;

        $this->analyse([__DIR__ . '/data/version.php'], $errors);
    }

    /**
     * @return iterable<array{string, list<array{0: string, 1: int, 2?: string|null}>}>
     */
    public static function provideSemanticVersions(): iterable
    {
        $tip = "Calculated reference version is '1.0.0.0'.\n\n   See also:\n https://github.com/staabm/phpstan-todo-by#reference-version";

        yield [
            'nextMajor', // we assume this resolves to 1.0
            [
                [
                    'Version requirement >=1.0 satisfied.',
                    12,
                    $tip,
                ],
            ],
        ];
    }
}
