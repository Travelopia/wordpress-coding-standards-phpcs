<?php
/**
 * Sniff: PreferInstofOverIsWPErrorSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if `is_wp_error` is used.
 */
class PreferInstofOverIsWPErrorSniff implements Sniff
{
	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array
	{
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
	public function process( File $phpcsFile, $stackPtr ): void
	{
		// Get tokens.
		$tokens = $phpcsFile->getTokens();

		if ( 'is_wp_error' === $tokens[ $stackPtr ]['content'] ) {
			$phpcsFile->addWarningOnLine(
				'Use `instanceof WP_Error` instead of `is_wp_error()`.',
				$tokens[ $stackPtr ]['line'],
				'UseInstanceOf',
			);
		}
	}
}
