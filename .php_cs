<?php

declare(strict_types=1);

$projectFilesFinder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

$executableFilesFinder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->depth('< 2')
    ->name('/^[^.]+$/')
    ->contains('@^#!(/usr(/local)?)?/bin/(env )?php([5-7](\.[0-9]+))?\n@')
;

$configFilesFinder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->depth('< 1')
    ->ignoreDotFiles(false)
    ->notName('/^[^.]/')
    ->name('/^\.[^.]+$/')
    ->contains('@^<\?php[\n ]@')
;

return PhpCsFixer\Config::create()
    ->setFinder(array_replace(
        iterator_to_array($projectFilesFinder),
        iterator_to_array($executableFilesFinder),
        iterator_to_array($configFilesFinder)
    ))
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP71Migration'                        => true,
        '@PHP71Migration:risky'                  => true,
        '@PHPUnit60Migration:risky'              => true,
        '@PSR2'                                  => true,
        '@Symfony'                               => true,
        'align_multiline_comment'                => ['comment_type' => 'all_multiline'],
        'array_indentation'                      => true,
        'array_syntax'                           => ['syntax' => 'short'],
        'backtick_to_shell_exec'                 => true,
        'binary_operator_spaces'                 => ['operators' => array_fill_keys(['=>', '='], 'align_single_space_minimal')],
        'combine_consecutive_issets'             => true,
        'combine_consecutive_unsets'             => true,
        'concat_space'                           => ['spacing' => 'one'],
        'escape_implicit_backslashes'            => true,
        'fully_qualified_strict_types'           => true,
        'heredoc_to_nowdoc'                      => true,
        'is_null'                                => true,
        'linebreak_after_opening_tag'            => true,
        'method_argument_space'                  => ['ensure_fully_multiline' => true],
        'method_chaining_indentation'            => true,
        'modernize_types_casting'                => true,
        'multiline_comment_opening_closing'      => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'no_alternative_syntax'                  => true,
        'no_null_property_initialization'        => true,
        'no_php4_constructor'                    => true,
        'no_unreachable_default_argument_value'  => true,
        'not_operator_with_space'                => true,
        'ordered_class_elements'                 => ['sortAlgorithm' => 'alpha'],
        'ordered_imports'                        => true,
        'php_unit_strict'                        => true,
        'phpdoc_no_alias_tag'                    => ['type' => 'var', 'link' => 'see'],
        'phpdoc_order'                           => true,
        'psr4'                                   => true,
        'simplified_null_return'                 => true,
        'strict_comparison'                      => true,
        'strict_param'                           => true,
        'string_line_ending'                     => true,
        'yoda_style'                             => false,
    ])
;
