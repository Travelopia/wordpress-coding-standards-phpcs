<?php
/**
 * Sniff: FunctionArgsDefaultValueSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Functions;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to check if function args have a default value.
 */
class FunctionArgsDefaultValueSniff implements Sniff {

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

		// Get close parenthesis.
		$close_parenthesis = $phpcsFile->findNext( [ T_CLOSE_PARENTHESIS ], $stackPtr );
		if ( false === $close_parenthesis ) {
			return;
		}

		// Start counting variables and equals.
		$variables = 0;
		$equals    = 0;

		// Get all tokens before close parenthesis.
		for ( $i = $stackPtr; $i <= $close_parenthesis; $i ++ ) {
			if ( 'T_VARIABLE' === $tokens[ $i ]['type'] ) {
				$variables ++;
			} elseif ( 'T_EQUAL' === $tokens[ $i ]['type'] ) {
				$equals ++;
			}
		}

		// If the number of variables and equals don't match, then they don't have a default value.
		if ( $variables !== $equals ) {
			$phpcsFile->addWarningOnLine(
				'All function args must have a default value.',
				$tokens[ $stackPtr ]['line'],
				'Missing'
			);
		}
	}

}
