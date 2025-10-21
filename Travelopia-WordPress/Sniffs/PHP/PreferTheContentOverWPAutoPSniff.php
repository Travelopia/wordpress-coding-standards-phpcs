<?php
/**
 * Sniff: PreferTheContentOverWPAutoPSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if `wpautop` is used.
 */
class PreferTheContentOverWPAutoPSniff implements Sniff
{
	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array
	{
		return [ T_STRING ];
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

		if ( 'wpautop' === $tokens[ $stackPtr ]['content'] ) {
			$phpcsFile->addWarningOnLine(
				'Use `apply_filters( \'the_content\', $content )` instead of `wpautop()`.',
				$tokens[ $stackPtr ]['line'],
				'UseTheContent',
			);
		}
	}
}
