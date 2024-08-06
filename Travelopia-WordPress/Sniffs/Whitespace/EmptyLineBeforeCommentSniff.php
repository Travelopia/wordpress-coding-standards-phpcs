<?php
/**
 * Sniff: EmptyLineBeforeCommentSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if an empty line is added before a comment.
 */
class EmptyLineBeforeCommentSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_COMMENT ];
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

		// Get previous tokens.
		$previous_token = $phpcsFile->findPrevious( [ T_SEMICOLON, T_CLOSE_CURLY_BRACKET ], $stackPtr );
		if ( ! is_int( $previous_token ) ) {
			return;
		}

		// If previous token is in the preceding line, throw an error.
		if ( $tokens[ $previous_token ]['line'] === $tokens[ $stackPtr ]['line'] - 1 ) {
			// Add exceptions to this rule.
			$previous_token = $phpcsFile->findPrevious( [ T_OPEN_CURLY_BRACKET ], $stackPtr );

			if ( ! is_int( $previous_token ) || $tokens[ $previous_token ]['line'] !== $tokens[ $stackPtr ]['line'] - 1 ) {
				$phpcsFile->addWarningOnLine(
					'Add empty line before comment.',
					$tokens[ $stackPtr ]['line'],
					'Missing'
				);
			}
		}
	}
}
