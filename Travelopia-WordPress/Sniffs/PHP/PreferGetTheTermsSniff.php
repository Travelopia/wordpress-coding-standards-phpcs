<?php
/**
 * Sniff: PreferGetTheTermsSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if `wp_get_object_terms` is used.
 */
class PreferGetTheTermsSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_STRING ];
	}

	/**
	 * Process the sniff.
	 *
	 * @param File $phpcsFile The file being processed.
	 * @param int  $stackPtr  Stack pointer.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ): void {
		// Get tokens.
		$tokens = $phpcsFile->getTokens();

		// Add an error if `wp_get_object_terms` is encountered.
		if ( 'wp_get_object_terms' === $tokens[ $stackPtr ]['content'] ) {
			$phpcsFile->addWarningOnLine(
				'Use `get_the_terms()` instead of `wp_get_object_terms()`.',
				$tokens[ $stackPtr ]['line'],
				'AvoidWPGetObjectTerms'
			);
		}
	}

}
