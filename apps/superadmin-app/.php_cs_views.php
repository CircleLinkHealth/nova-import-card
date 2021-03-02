<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

$header = <<<'EOF'
This file is part of CarePlan Manager by CircleLink Health.
EOF;

$finders = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/resources/views',
    ]);

$config = PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRiskyAllowed(false)
    ->setRules([
        '@Symfony'                => true,
        '@PSR1'                   => true,
        '@PSR2'                   => true,
        'align_multiline_comment' => true,
        'array_indentation'       => true,
        'array_syntax'            => ['syntax' => 'short'],
        'binary_operator_spaces'  => [
            'operators' => [
                '='   => 'align_single_space_minimal',
                '=='  => 'align_single_space_minimal',
                '===' => 'align_single_space_minimal',
                '+='  => 'align_single_space_minimal',
                '=>'  => 'align_single_space_minimal',
                '|'   => 'no_space',
            ],
        ],
        'combine_consecutive_issets'        => true,
        'combine_consecutive_unsets'        => true,
        'compact_nullable_typehint'         => true,
        'fully_qualified_strict_types'      => false,
        'header_comment'                    => ['header' => $header],
        'list_syntax'                       => ['syntax' => 'short'],
        'method_argument_space'             => ['on_multiline' => 'ensure_fully_multiline'],
        'method_chaining_indentation'       => true,
        'multiline_comment_opening_closing' => true,
        'no_binary_string'                  => true,
        'no_empty_comment'                  => true,
        'no_empty_phpdoc'                   => true,
        'no_extra_blank_lines'              => [
            'tokens' => [
                'break',
                'continue',
                'extra',
                'return',
                'throw',
                'use',
                'parenthesis_brace_block',
                'square_brace_block',
                'curly_brace_block',
            ],
        ],
        'no_null_property_initialization' => true,
        'no_short_echo_tag'               => false,
        'no_superfluous_elseif'           => true,
        'no_unused_imports'               => false,
        'no_useless_else'                 => true,
        'no_useless_return'               => true,
        'not_operator_with_space'         => true,
        'ordered_class_elements'          => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
            'sortAlgorithm' => 'alpha',
        ],
        'ordered_imports'                               => true,
        'phpdoc_add_missing_param_annotation'           => true,
        'php_unit_method_casing'                        => ['case' => 'snake_case'],
        'phpdoc_order'                                  => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types_order'                            => [
            'null_adjustment' => 'always_last',
            'sort_algorithm'  => 'alpha',
        ],
        'return_assignment'           => true,
        'semicolon_after_instruction' => true,
        'single_line_comment_style'   => true,
        'yoda_style'                  => true,
    ])
    ->setFinder($finders);

return $config;
