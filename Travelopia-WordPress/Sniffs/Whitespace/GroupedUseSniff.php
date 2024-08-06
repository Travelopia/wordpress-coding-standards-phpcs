<?php
/**
 * Sniff: GroupedUseSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Whitespace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if `use` statements are grouped together, separated by newlines.
 */
class GroupedUseSniff implements Sniff {

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

		// Check if there are enough newlines.
		if ( empty( $tokens[ $stackPtr - 1 ] ) || empty( $tokens[ $stackPtr - 2 ] ) ) {
			return;
		}

		// Check if we don't have 2 newline characters before `use` statement.
		if ( ! str_ends_with( $tokens[ $stackPtr - 1 ]['content'], "\n" ) || "\n" !== $tokens[ $stackPtr - 2 ]['content'] ) {
			// Get the previous `use` statement.
			$prev_use = $phpcsFile->findPrevious( [ T_USE ], $stackPtr - 1 );

			// If the previous `use` statement isn't part of the same group, show an error.
			if ( in_array( $tokens[ $stackPtr + 2 ]['content'], [ 'function', 'const' ], true ) && $tokens[ $stackPtr + 2 ]['content'] !== $tokens[ $prev_use + 2 ]['content'] ) {
				$phpcsFile->addWarningOnLine(
					'Add empty line before `use` group.',
					$tokens[ $stackPtr ]['line'],
					'AddEmptyLineBeforeUseGroup'
				);
				return;
			}

			// If the previous line is a `use` is in the same group, but not the line immediately preceding it, show an error.
			if ( is_int( $prev_use ) && T_USE === $tokens[ $prev_use ]['code'] && $tokens[ $prev_use ]['line'] !== $tokens[ $stackPtr ]['line'] - 1 ) {
				// Except if an open parenthesis is on the same line. Example: function() use () {}.
				$open_paranthesis = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $stackPtr );
				if ( is_int( $open_paranthesis ) && $tokens[ $open_paranthesis ]['line'] === $tokens[ $stackPtr ]['line'] ) {
					return;
				}

				$phpcsFile->addWarningOnLine(
					'Add empty line before `use` group.',
					$tokens[ $stackPtr ]['line'],
					'AddEmptyLineBeforeUseGroup'
				);
			}
		}
	}

}
