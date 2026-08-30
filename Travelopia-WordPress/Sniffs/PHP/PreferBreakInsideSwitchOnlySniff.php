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

		// Set Start and End pointers.
		$searchStartPtr = $stackPtr + 1;
		$caseEndPtr     = $tokens[ $stackPtr ]['scope_closer'];

		// Search for continue inside the case.
		do {
			// Find the next continue.
			$continuePtr = $phpcsFile->findNext( T_CONTINUE, $searchStartPtr, $caseEndPtr + 1 );

			// If continue is inside a loop, ignore it.
			if ( false !== $continuePtr ) {
				// Search for the loop in which the continue is present.
				$loopStartPtr = $phpcsFile->findPrevious( [ T_WHILE, T_FOR, T_FOREACH, T_DO ], $continuePtr, $stackPtr );
				$loopEndPtr   = $tokens[ $loopStartPtr ]['scope_closer'];

				// If the continue is inside a loop, ignore it.
				if ( $loopStartPtr && $continuePtr > $loopStartPtr && $continuePtr < $loopEndPtr ) {
					$searchStartPtr = $loopEndPtr + 1;
					continue;
				}

				// Display warning.
				$phpcsFile->addWarningOnLine(
					'Use `break` or `continue x` where x is the nesting level to break out of the switch.',
					$tokens[ $continuePtr ]['line'],
					'UseBreakInsideSwitch'
				);

				// Set the search pointer to the next token.
				$searchStartPtr = $continuePtr + 1;
			}
		} while ( false !== $continuePtr );
	}
}
