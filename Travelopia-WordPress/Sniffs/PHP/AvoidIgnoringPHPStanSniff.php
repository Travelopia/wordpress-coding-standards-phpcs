<?php
/**
 * Sniff: AvoidIgnoringPHPStanSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if PHPStan errors are ignored.
 */
class AvoidIgnoringPHPStanSniff implements Sniff
{
	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array
	{
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
	public function process( File $phpcsFile, $stackPtr ): void
	{
		// Get tokens.
		$tokens = $phpcsFile->getTokens();

		// Check for PHPStan ignores and add a warning.
		if ( str_contains( $tokens[ $stackPtr ]['content'], '@phpstan-ignore' ) || str_contains( $tokens[ $stackPtr ]['content'], '@phpstan-ignore-line' ) ) {
			$phpcsFile->addWarningOnLine(
				"Don't ignore PHPStan errors. Try to address the issue by adding additional checks.",
				$tokens[ $stackPtr ]['line'],
				'Avoid',
			);
		}
	}
}
