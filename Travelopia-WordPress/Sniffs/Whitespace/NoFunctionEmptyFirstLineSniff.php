<?php
/**
 * Sniff: NoFunctionEmptyFirstLine.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if the first line in a function is not empty.
 */
class NoFunctionEmptyFirstLine implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_FUNCTION ];
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

		// Get first open bracket.
		$open_bracket = $phpcsFile->findNext( T_OPEN_CURLY_BRACKET, $stackPtr );
		if ( ! is_int( $open_bracket ) ) {
			return;
		}

		// Check if there are enough newlines.
		if ( empty( $tokens[ $open_bracket + 1 ] ) || empty( $tokens[ $open_bracket + 2 ] ) ) {
			return;
		}

		// Check if there is only one newline after open bracket.
		if ( "\n" === $tokens[ $open_bracket + 1 ]['content'] && "\n" === $tokens[ $open_bracket + 2 ]['content'] ) {
			$phpcsFile->addWarningOnLine(
				'First line of function must not be empty.',
				$tokens[ $stackPtr ]['line'] + 1,
				'EmptyFirstLine'
			);
		}
	}
}
