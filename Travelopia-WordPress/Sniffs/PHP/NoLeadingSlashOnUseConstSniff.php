<?php
/**
 * Sniff: NoLeadingSlashOnUseConstSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if `use` statements have slash prefix for namespaces.
 */
class NoLeadingSlashOnUseConstSniff implements Sniff
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

		// Ignore tokens that are not `const` or `function`.
		if ( 'const' !== $tokens[ $stackPtr ]['content'] && 'function' !== $tokens[ $stackPtr ]['content'] ) {
			return;
		}

		// Check if we are within a `use` statement.
		$previous_use = $phpcsFile->findPrevious( T_USE, $stackPtr );

		if ( ! is_int( $previous_use ) || $tokens[ $previous_use ]['line'] !== $tokens[ $stackPtr ]['line'] ) {
			return;
		}

		// Check if a namespace separator is 2 characters after this one.
		if ( ! empty( $tokens[ $stackPtr + 2 ] ) && T_NS_SEPARATOR === $tokens[ $stackPtr + 2 ]['code'] ) {
			$phpcsFile->addWarningOnLine(
				'Avoid prefixing namespace with a slash.',
				$tokens[ $stackPtr ]['line'],
				'AvoidSlash',
			);
		}
	}
}
