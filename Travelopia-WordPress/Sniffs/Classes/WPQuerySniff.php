<?php
/**
 * Sniff: WPQuerySniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniffs related to WP_Query.
 */
class WPQuerySniff implements Sniff
{
	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array
	{
		return [ T_NEW ];
	}

	/**
	 * Process the sniff.
	 *
	 * @param File $phpcsFile The file being processed.
	 * @param int  $stackPtr  Stack pointer.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ): void
	{
		// Get tokens.
		$tokens = $phpcsFile->getTokens();

		// Get the next string.
		$next_string = $phpcsFile->findNext( [ T_STRING ], $stackPtr );

		// Check if WP_Query is the string after `new`.
		if ( false === $next_string || 'WP_Query' !== $tokens[ $next_string ]['content'] ) {
			return;
		}

		// Get open parenthesis.
		$open_parenthesis = $phpcsFile->findNext( [ T_OPEN_PARENTHESIS ], $stackPtr );

		if ( false === $open_parenthesis ) {
			return;
		}

		// Get close parenthesis.
		$close_parenthesis = $phpcsFile->findNext( [ T_CLOSE_PARENTHESIS ], $open_parenthesis );

		if ( false === $close_parenthesis ) {
			return;
		}

		// Keep track of args.
		$required_args = [
			"'post_type'" => [
				'exists'     => false,
				'error'      => "Missing 'post_type' argument.",
				'error_code' => 'MissingPostType',
			],
			"'no_found_rows'" => [
				'exists'     => false,
				'error'      => "Missing 'no_found_rows' argument.",
				'error_code' => 'MissingNoFoundRows',
			],
			"'update_post_meta_cache'" => [
				'exists'     => false,
				'error'      => "Missing 'update_post_meta_cache' argument.",
				'error_code' => 'MissingPostMetaCache',
			],
			"'update_post_term_cache'" => [
				'exists'     => false,
				'error'      => "Missing 'update_post_term_cache' argument.",
				'error_code' => 'MissingPostTermCache',
			],
			"'fields'" => [
				'exists'     => false,
				'error'      => "Missing 'fields' argument.",
				'error_code' => 'MissingFields',
			],
			"'posts_per_page'" => [
				'exists'     => false,
				'error'      => "Missing 'posts_per_page' argument.",
				'error_code' => 'MissingPostsPerPage',
			],
			"'ignore_sticky_posts'" => [
				'exists'     => false,
				'error'      => "Missing 'ignore_sticky_posts' argument.",
				'error_code' => 'MissingIgnoreStickyPosts',
			],
		];

		// Tax query stuff.
		$tax_query        = 0;
		$taxonomies       = 0;
		$include_children = 0;

		// Traverse all tokens before close parenthesis.
		for ( $i = $open_parenthesis; $i <= $close_parenthesis; ++$i  ) {
			if ( 'T_VARIABLE' === $tokens[ $i ]['type'] ) {
				// Bail if a variable is found within parenthesis.
				return;
			}

			if ( 'T_CONSTANT_ENCAPSED_STRING' !== $tokens[ $i ]['type'] ) {
				continue;
			}

			if ( array_key_exists( $tokens[ $i ]['content'], $required_args ) ) {
				$required_args[ $tokens[ $i ]['content'] ]['exists'] = true;
			}

			if ( "'tax_query'" === $tokens[ $i ]['content'] ) {
				++$tax_query;
			} elseif ( "'taxonomy'" === $tokens[ $i ]['content'] ) {
				++$taxonomies;
			} elseif ( "'include_children'" === $tokens[ $i ]['content'] ) {
				++$include_children;
			}
		}

		// Add warnings for missing args.
		foreach ( $required_args as $required_arg ) {
			if ( false === $required_arg['exists'] ) {
				$phpcsFile->addWarningOnLine(
					$required_arg['error'],
					$tokens[ $stackPtr ]['line'],
					$required_arg['error_code'],
				);
			}
		}

		// Tax query warnings.
		if ( 1 === $tax_query && $taxonomies !== $include_children ) {
			$phpcsFile->addWarningOnLine(
				"'include_children' is required for each taxonomy query.",
				$tokens[ $stackPtr ]['line'],
				'MissingIncludeChildren',
			);
		}
	}
}
