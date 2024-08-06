<?php
/**
 * Sniff: NamespaceLeadingSlashSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to avoid leading slash on namespaces.
 */
class NamespaceLeadingSlashSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_NS_SEPARATOR ];
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

		// Get return type.
		$previous_whitespace = $phpcsFile->findPrevious( [ T_WHITESPACE ], $stackPtr - 1 );
		if ( is_int( $previous_whitespace ) && $tokens[ $previous_whitespace ]['column'] === $tokens[ $stackPtr ]['column'] - 1 ) {
			$phpcsFile->addWarningOnLine(
				'Avoid leading slashes on namespaces. Add a `use` statement at the top of the file instead.',
				$tokens[ $stackPtr ]['line'],
				'Avoid'
			);
		}
	}

}
