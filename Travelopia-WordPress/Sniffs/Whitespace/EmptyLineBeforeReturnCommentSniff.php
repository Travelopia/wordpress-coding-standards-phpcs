<?php
/**
 * Sniff: EmptyLineBeforeReturnCommentSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if an empty line is added above a `@return` comment.
 */
class EmptyLineBeforeReturnCommentSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_DOC_COMMENT_TAG ];
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

		// Check if current tag is `@return`.
		if ( '@return' !== $tokens[ $stackPtr ]['content'] ) {
			return;
		}

		// Get the previous doc comment tag.
		$previous_tag = $phpcsFile->findPrevious( [ T_DOC_COMMENT_TAG ], $stackPtr - 1 );
		if ( false === $previous_tag ) {
			return;
		}

		// If it was on the previous line, it's an error.
		if ( $tokens[ $previous_tag ]['line'] === $tokens[ $stackPtr ]['line'] - 1 ) {
			$phpcsFile->addWarningOnLine(
				'Add empty line before @return.',
				$tokens[ $stackPtr ]['line'],
				'Missing'
			);
		}
	}
}
