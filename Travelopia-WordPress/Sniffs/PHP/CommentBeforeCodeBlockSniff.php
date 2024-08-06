<?php
/**
 * Sniff: CommentBeforeCodeBlockSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if a comment is added before each code block.
 */
class CommentBeforeCodeBlockSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_WHITESPACE ];
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

		// Look for two consecutive newline characters.
		if ( "\n" === $tokens[ $stackPtr ]['content'] && "\n" === $tokens[ $stackPtr - 1 ]['content'] ) {
			// Exclude this from certain statements.
			$exclude_tokens = $phpcsFile->findPrevious( [ T_USE, T_CONST, T_NAMESPACE ], $stackPtr );
			if ( is_int( $exclude_tokens ) && $tokens[ $exclude_tokens ]['line'] === $tokens[ $stackPtr ]['line'] - 1 ) {
				return;
			}

			// Look for the next tokens.
			$next_tokens = $phpcsFile->findNext( [ T_COMMENT, T_DOC_COMMENT_OPEN_TAG, T_NAMESPACE, T_CLOSE_CURLY_BRACKET ], $stackPtr );

			// Check if the next comment isn't in the next line.
			if ( false === $next_tokens || $tokens[ $next_tokens ]['line'] !== $tokens[ $stackPtr + 1 ]['line'] ) {
				$phpcsFile->addWarningOnLine(
					'Add a comment before each code block.',
					$tokens[ $stackPtr ]['line'] + 1,
					'Missing'
				);
			}
		}
	}

}
