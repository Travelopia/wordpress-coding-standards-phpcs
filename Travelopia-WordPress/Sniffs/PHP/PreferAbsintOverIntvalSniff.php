<?php
/**
 * Sniff: PreferAbsintOverIntvalSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if `intval` is used.
 */
class PreferAbsintOverIntvalSniff implements Sniff {

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

		if ( 'intval' === $tokens[ $stackPtr ]['content'] ) {
			$phpcsFile->addWarningOnLine(
				'Use `absint()` instead of `intval()`.',
				$tokens[ $stackPtr ]['line'],
				'UseAbsInt'
			);
		}
	}

}
