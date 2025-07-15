<?php

declare(strict_types = 1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'bootstrap', 'storage'])
    ->name('*.php')
    ->notName('*.blade.php');

$config = new Config();

return $config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'clean_namespace' => true,
        'comment_to_phpdoc' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_after_namespace' => true,
        'blank_line_before_statement' => [
            'statements' => ['return'],
        ],
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'single_space',
            ],
        ],
        'cast_spaces' => [
            'space' => 'single',
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'braces' => [
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
            ],
        ],
        'declare_equal_normalize' => [
            'space' => 'single',
        ],
        'function_declaration' => [
            'closure_function_spacing' => 'one',
        ],
        'include' => true,
        'lowercase_cast' => true,
        'no_empty_statement' => true,
        'no_leading_import_slash' => true,
        'no_trailing_comma_in_list_call' => false,
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_align' => false,
        'phpdoc_indent' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'single_trait_insert_per_statement' => true,
        'ternary_operator_spaces' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments'],
        ],
        'return_type_declaration' => [
            'space_before' => 'none',
        ],
        'global_namespace_import' => [
            'import_constants' => true,
            'import_functions' => true,
            'import_classes' => true,
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
            'after_heredoc' => false,
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'case',
                'property_public',
                'property_protected',
                'property_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public_static',
                'method_public',
                'method_protected_static',
                'method_protected',
                'method_private_static',
                'method_private',
            ],
            'sort_algorithm' => 'none',
        ],
        'void_return' => true,
        'function_typehint_space' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'array_indentation' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'extra',
                'return',
                'switch',
                'throw',
            ],
        ],
        'phpdoc_no_package' => true,
        'phpdoc_summary' => true,
        'phpdoc_var_without_name' => true,
        'strict_comparison' => true,
        'no_mixed_echo_print' => [
            'use' => 'echo',
        ],
        'no_trailing_whitespace' => true,
        'single_line_after_imports' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'whitespace_after_comma_in_array' => true,
        'fully_qualified_strict_types' => true,
        'declare_strict_types' => true,
        'native_function_casing' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'single_quote' => true,
        'no_spaces_around_offset' => true,
        'no_leading_namespace_whitespace' => true,
    ]);