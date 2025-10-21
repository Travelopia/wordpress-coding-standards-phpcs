<?php

namespace Travelopia\WordPressCodingStandards;

use PhpCsFixer\Config;
use Travelopia\WordPressCodingStandards\Fixers\BlankLineAfterControlStructureFixer;
use Travelopia\WordPressCodingStandards\Fixers\BlankLineBeforeCommentFixer;
use Travelopia\WordPressCodingStandards\Fixers\CommentPunctuationFixer;
use Travelopia\WordPressCodingStandards\Fixers\DocBlockOnNewLineFixer;
use Travelopia\WordPressCodingStandards\Fixers\SpacesInsideArrayBracketsFixer;

/**
 * Helper class to easily create a PHP-CS-Fixer config with Travelopia custom fixers.
 */
class TravelopiaFixersConfig
{
	/**
	 * Create a new PHP-CS-Fixer config with Travelopia custom fixers registered.
	 *
	 * @return Config
	 */
	public static function create(): Config
	{
		$config = new Config();
		$config->registerCustomFixers( self::getCustomFixers() );

		return $config;
	}

	/**
	 * Get all custom fixers.
	 *
	 * @return array
	 */
	public static function getCustomFixers(): array
	{
		return [
			new SpacesInsideArrayBracketsFixer(),
			new BlankLineBeforeCommentFixer(),
			new CommentPunctuationFixer(),
			new BlankLineAfterControlStructureFixer(),
			new DocBlockOnNewLineFixer(),
		];
	}

	/**
	 * Get the default ruleset used by Travelopia.
	 *
	 * @return array
	 */
	public static function getRules(): array
	{
		return [
			// PSR-12 preset as base.
			'@PSR12' => true,

			// Override indentation to use tabs.
			'indentation_type' => true,

			// No blank line after opening PHP tag.
			'blank_line_after_opening_tag' => false,

			// Import ordering.
			'ordered_imports' => [
				'sort_algorithm' => 'alpha',
				'imports_order'  => [ 'class', 'function', 'const' ],
			],

			// Force use statements instead of fully qualified names.
			'fully_qualified_strict_types' => [
				'import_symbols' => true,
			],
			'no_unused_imports'       => true,
			'global_namespace_import' => [
				'import_classes'   => true,
				'import_constants' => true,
				'import_functions' => true,
			],

			// Spaces inside parentheses.
			'spaces_inside_parentheses' => [ 'space' => 'single' ],

			// Class attributes separation.
			'class_attributes_separation' => [
				'elements' => [
					'method'   => 'one',
					'property' => 'one',
					'const'    => 'one',
				],
			],

			// Single line comment spacing.
			'single_line_comment_spacing' => true,

			// Nullable type declaration.
			'nullable_type_declaration_for_default_null_value' => true,

			// Array syntax.
			'array_syntax' => [
				'syntax' => 'short',
			],

			// Custom rule: spaces inside array brackets.
			'Travelopia/spaces_inside_array_brackets' => true,

			// Custom rule: blank line before comments.
			'Travelopia/blank_line_before_comment' => true,

			// Custom rule: comments must end with punctuation.
			'Travelopia/comment_punctuation' => true,

			// Custom rule: blank line after control structures.
			'Travelopia/blank_line_after_control_structure' => true,

			// Custom rule: docblock on new line.
			'Travelopia/docblock_on_new_line' => true,

			// Array indentation.
			'array_indentation' => true,

			// Binary operator spaces with alignment.
			'binary_operator_spaces' => [
				'operators' => [
					'='  => 'align_single_space_minimal',
					'=>' => 'align_single_space_minimal',
				],
			],

			// Operators on new lines go at the beginning (left side).
			'operator_linebreak' => [
				'only_booleans' => false,
				'position'      => 'beginning',
			],

			// Trailing comma in multiline.
			'trailing_comma_in_multiline' => [
				'elements' => [ 'arrays', 'arguments', 'parameters' ],
			],

			// Concat space.
			'concat_space' => [
				'spacing' => 'one',
			],

			// PHPDoc line span.
			'phpdoc_line_span' => [
				'const'    => 'single',
				'property' => 'single',
				'method'   => 'multi',
			],

			// PHPDoc separation.
			'phpdoc_separation' => true,

			// PHPDoc summary.
			'phpdoc_summary' => true,

			// PHPDoc alignment.
			'phpdoc_align' => [ 'align' => 'vertical' ],

			// PHPDoc indentation.
			'phpdoc_indent' => true,

			// No extra blank lines.
			'no_extra_blank_lines' => [
				'tokens' => [
					'extra',
					'use',
					'square_brace_block',
					'curly_brace_block',
					'return',
					'throw',
					'break',
					'continue',
				],
			],

			// No trailing whitespace.
			'no_trailing_whitespace'                     => true,
			'no_whitespace_before_comma_in_array'        => true,
			'no_space_around_double_colon'               => true,
			'no_singleline_whitespace_before_semicolons' => true,
			'whitespace_after_comma_in_array'            => [ 'ensure_single_space' => true ],

			// Enforce Yoda conditions.
			'yoda_style' => [
				'equal'            => true,
				'identical'        => true,
				'less_and_greater' => true,
			],

			// Use single quotes for strings.
			'single_quote' => true,

			// Enforce strict comparison operators.
			'strict_comparison' => true,

			// Enforce strict parameter typing.
			'strict_param' => true,

			// Blank line before statements.
			'blank_line_before_statement' => [
				'statements' => [
					'if',
					'for',
					'foreach',
					'switch',
					'while',
					'do',
					'try',
				],
			],

			// Use pre-increment/decrement operators.
			'increment_style' => [ 'style' => 'pre' ],

			// Method argument spacing - ensure fully multiline when there are newlines.
			'method_argument_space' => [
				'on_multiline'                     => 'ensure_fully_multiline',
				'keep_multiple_spaces_after_comma' => false,
			],
		];
	}
}
