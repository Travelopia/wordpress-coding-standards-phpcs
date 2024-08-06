<?php
/**
 * Sniff: AvoidUseCommasSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Whitespace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if `use` statements are grouped together, separated by newlines.
 */
class AvoidUseCommasSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_USE ];
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

		// Find semicolon.
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $stackPtr - 1 );
		if ( ! is_int( $semicolon ) ) {
			return;
		}

		// Check for open parenthesis (to avoid use in functions).
		$open_bracket = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $stackPtr - 1 );
		if ( is_int( $open_bracket ) && $open_bracket < $semicolon ) {
			return;
		}

		// Find comma.
		$comma = $phpcsFile->findNext( T_COMMA, $stackPtr - 1 );
		if ( ! is_int( $comma ) ) {
			return;
		}

		// Check if the comma is before semicolon.
		if ( $comma < $semicolon ) {
			$phpcsFile->addWarningOnLine(
				'Avoid commas in `use`.',
				$tokens[ $stackPtr ]['line'],
				'AvoidCommasInUse'
			);
		}
	}

}
