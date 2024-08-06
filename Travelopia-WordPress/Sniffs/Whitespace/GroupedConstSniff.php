<?php
/**
 * Sniff: GroupedConstSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Whitespace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if constants are grouped together, separated by newlines.
 */
class GroupedConstSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_CONST ];
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

		// Check if there are enough newlines.
		if ( empty( $tokens[ $stackPtr - 1 ] ) || empty( $tokens[ $stackPtr - 2 ] ) ) {
			return;
		}

		// Check if we don't have 2 newline characters before `const` statement.
		if ( ! str_ends_with( $tokens[ $stackPtr - 1 ]['content'], "\n" ) || "\n" !== $tokens[ $stackPtr - 2 ]['content'] ) {
			// Get the previous `const` statement.
			$prev_const = $phpcsFile->findPrevious( [ T_CONST ], $stackPtr - 1 );

			// If the previous line is a `const`, but not the line immediately preceding it, show an error.
			if (
				! is_int( $prev_const )
				|| ( T_CONST === $tokens[ $prev_const ]['code'] && $tokens[ $prev_const ]['line'] !== $tokens[ $stackPtr ]['line'] - 1 )
			) {
				$phpcsFile->addWarningOnLine(
					'Add empty line before `const` group.',
					$tokens[ $stackPtr ]['line'],
					'AddEmptyLineBeforeConstGroup'
				);
			}
		}
	}

}
