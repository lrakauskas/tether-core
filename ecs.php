<?php

use Symplify\EasyCodingStandard\Config\ECSConfig;

$skip = [
    PhpCsFixer\Fixer\Basic\BracesPositionFixer::class,
    PhpCsFixer\Fixer\Basic\EncodingFixer::class,
    PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer::class,
    PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer::class,
    PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer::class,
    PhpCsFixer\Fixer\Import\OrderedImportsFixer::class,
    PhpCsFixer\Fixer\Import\SingleLineAfterImportsFixer::class,
    PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer::class,
    PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer::class,
    PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer::class,
    PhpCsFixer\Fixer\Operator\ConcatSpaceFixer::class,
    PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer::class,
    PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer::class,
    PhpCsFixer\Fixer\Whitespace\LineEndingFixer::class,
    PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer::class,
    PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer::class,
];

$paths = array_values(array_filter([
    __DIR__.'/config',
    __DIR__.'/database',
    __DIR__.'/routes',
    __DIR__.'/src',
    __DIR__.'/tests',
], static fn (string $path): bool => is_dir($path)));

return ECSConfig::configure()
    ->withPaths($paths)
    ->withRootFiles()
    ->withCache(sys_get_temp_dir().DIRECTORY_SEPARATOR.basename(__DIR__).'-ecs-cache', basename(__DIR__))
    ->withSkip($skip)
    ->withPreparedSets(psr12: true);
