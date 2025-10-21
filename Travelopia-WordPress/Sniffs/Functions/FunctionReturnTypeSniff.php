<?php
/**
 * Sniff: FunctionReturnTypeSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if functions have a return type.
 */
class FunctionReturnTypeSniff implements Sniff
{
	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array
	{
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
	public function process( File $phpcsFile, $stackPtr ): void
	{
		// Get tokens.
		$tokens = $phpcsFile->getTokens();

		// Ignore constructors.
		$next_string = $phpcsFile->findNext( [ T_STRING ], $stackPtr );

		if ( is_int( $next_string ) && '__construct' === $tokens[ $next_string ]['content'] ) {
			return;
		}

		// Get return type.
		$next_return_type = $phpcsFile->findNext( [ T_COLON ], $stackPtr );

		if ( ! is_int( $next_return_type ) || $tokens[ $next_return_type ]['line'] !== $tokens[ $stackPtr ]['line'] ) {
			$phpcsFile->addWarningOnLine(
				'Functions must have a return type.',
				$tokens[ $stackPtr ]['line'],
				'Missing',
			);
		}
	}
}
