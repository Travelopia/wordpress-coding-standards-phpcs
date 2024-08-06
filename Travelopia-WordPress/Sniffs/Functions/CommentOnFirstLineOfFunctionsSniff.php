<?php
/**
 * Sniff: CommentOnFirstLineOfFunctionsSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Functions;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if the first line of a function contains a comment.
 */
class CommentOnFirstLineOfFunctionsSniff implements Sniff {

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

		// Ignore abstract functions.
		$abstract_token = $phpcsFile->findPrevious( [ T_ABSTRACT ], $stackPtr );
		if ( is_int( $abstract_token ) && $tokens[ $abstract_token ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			return;
		}

		// Get next comment.
		$next_comment = $phpcsFile->findNext( [ T_COMMENT, T_DOC_COMMENT_OPEN_TAG ], $stackPtr );
		if ( ! is_int( $next_comment ) || $tokens[ $next_comment ]['line'] !== $tokens[ $stackPtr ]['line'] + 1 ) {
			$phpcsFile->addWarningOnLine(
				'The first line of a function must contain a comment.',
				$tokens[ $stackPtr ]['line'] + 1,
				'Missing'
			);
		}
	}

}
