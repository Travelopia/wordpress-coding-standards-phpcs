<?php
/**
 * Sniff: PreferStrvalOverStringSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if a string is cast.
 */
class PreferStrvalOverStringSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_STRING_CAST ];
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
			'Use `strval()` instead of (string).',
			$tokens[ $stackPtr ]['line'],
			'UseAbsInt'
		);
	}

}
