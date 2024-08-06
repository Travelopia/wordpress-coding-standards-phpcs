<?php
/**
 * Sniff: PreferAbsintOverIntSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if an integer is cast.
 */
class PreferAbsintOverIntSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_INT_CAST ];
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

		$phpcsFile->addWarningOnLine(
			'Use `absint()` instead of (int).',
			$tokens[ $stackPtr ]['line'],
			'UseAbsInt'
		);
	}

}
