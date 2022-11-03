<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'var',
        'vendor',
        'config',
        'tools',
    ]);

return (new PhpCsFixer\Config())->setRules([
    // sets
    '@PER' => true,
    '@PHP81Migration' => true,

    // PHP81Migration override {{{
    'heredoc_indentation' => ['indentation' => 'same_as_start'],
    // }}} PHP81Migration override

    // specific rules
    'declare_strict_types' => true,
    'strict_comparison' => true,
    'strict_param' => true,
    'modernize_strpos' => true,
    'no_alias_functions' => [
        'sets' => ['@all'],
    ],
    'pow_to_exponentiation' => true,
    'set_type_to_cast' => true,
    'octal_notation' => true,
    'psr_autoloading' => true,
    'no_null_property_initialization' => true,
    'self_static_accessor' => true,
    'date_time_immutable' => true,
    'comment_to_phpdoc' => true,
    'no_superfluous_elseif' => true,
    'no_useless_else' => true,
    'date_time_create_from_format_call' => true,
    'no_unreachable_default_argument_value' => true,
    'regular_callable_call' => true,
    'combine_consecutive_issets' => true,
    'combine_consecutive_unsets' => true,
    'declare_parentheses' => true,
    'explicit_indirect_variable' => true,
    'no_unset_on_property' => true,
    'get_class_to_class_keyword' => true,
    'ternary_to_null_coalescing' => true,
    'php_unit_dedicate_assert' => true,
    'php_unit_dedicate_assert_internal_type' => true,
    'php_unit_expectation' => true,
    'php_unit_mock' => true,
    'php_unit_namespaced' => true,
    'php_unit_no_expectation_annotation' => true,
    'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
    'general_phpdoc_annotation_remove' => true,
    'phpdoc_order_by_value' => true, // overkill?
    'no_useless_return' => true,
    'simplified_null_return' => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'array_indentation' => true,
    'random_api_migration' => true,
    'doctrine_annotation_indentation' => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
    'phpdoc_var_annotation_correct_order' => true,
    'php_unit_strict' => true,
    'method_chaining_indentation' => true,
    'phpdoc_order' => true, // @param -> @throws -> @return
    'simple_to_complex_string_variable' => true,
    'explicit_string_variable' => true, // always use {$var} in string interpolation
    'escape_implicit_backslashes' => true, // ! second priority
    'static_lambda' => true,
    'heredoc_to_nowdoc' => true,
    'align_multiline_comment' => true,

    // parts of @symfony:risky {{{
    'dir_constant' => true,
    'function_to_constant' => true,
    'is_null' => true,
    'logical_operators' => true,
    'ternary_to_elvis_operator' => true,
    'modernize_types_casting' => true,
    'no_php4_constructor' => true,
    'ordered_traits' => true,
    'combine_nested_dirname' => true,
    'fopen_flag_order' => true,
    'fopen_flags' => true,
    'implode_call' => true,
    'no_useless_sprintf' => true,
    'php_unit_construct' => true,
    'php_unit_mock_short_will_return' => true,
    'php_unit_set_up_tear_down_visibility' => true,
    'php_unit_test_annotation' => true,
    'no_trailing_whitespace_in_string' => true,
    'string_length_to_empty' => true,
    'string_line_ending' => true,
    'no_unneeded_final_method' => true,
    // }}} parts of @symfony:risky
])->setFinder($finder);
