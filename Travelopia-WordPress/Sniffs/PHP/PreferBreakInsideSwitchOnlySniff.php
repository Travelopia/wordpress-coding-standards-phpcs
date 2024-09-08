<?php
/**
 * Sniff: PreferBreakInsideSwitchOnlySniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\PHP;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if only break is used inside a switch case, and not continue.
 */
class PreferBreakInsideSwitchOnlySniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_CASE ];
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

		// Search for continue inside the case.
		$continuePtr = $phpcsFile->findNext( T_CONTINUE, $stackPtr, $tokens[ $stackPtr ]['scope_closer'] + 1 );

		// run a loop for all continue statements inside the case
		while ( false !== $continuePtr ) {
			// Search for the loop in which the continue is present.
			$loopStartPtr = $phpcsFile->findPrevious( [ T_WHILE, T_FOR, T_FOREACH, T_DO ], $continuePtr, $stackPtr );
			$loopEndPtr   = $tokens[ $loopStartPtr ]['scope_closer'];

			// If the continue is inside a loop, ignore it.
			if ( $loopStartPtr && $continuePtr > $loopStartPtr && $continuePtr < $loopEndPtr ) {
				$continuePtr = $phpcsFile->findNext( T_CONTINUE, $continuePtr + 1, $tokens[ $stackPtr ]['scope_closer'] + 1 );
				continue;
			}

			// Add an error.
			$phpcsFile->addWarningOnLine(
				'Use `break` instead of `continue` inside a switch case.',
				$tokens[ $continuePtr ]['line'],
				'UseBreakInsideSwitch'
			);

			// Search for continue inside the case.
			$continuePtr = $phpcsFile->findNext( T_CONTINUE, $continuePtr + 1, $tokens[ $stackPtr ]['scope_closer'] + 1 );
		}
	}

}
