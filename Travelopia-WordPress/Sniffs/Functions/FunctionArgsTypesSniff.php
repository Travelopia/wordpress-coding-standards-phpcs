<?php
/**
 * Sniff: FunctionArgsTypesSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if function args have a type.
 */
class FunctionArgsTypesSniff implements Sniff
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

		// Get open parenthesis.
		$open_parenthesis = $phpcsFile->findNext( [ T_OPEN_PARENTHESIS ], $stackPtr );

		if ( false === $open_parenthesis ) {
			return;
		}

		// Get close parenthesis.
		$close_parenthesis = $phpcsFile->findNext( [ T_CLOSE_PARENTHESIS ], $open_parenthesis );

		if ( false === $close_parenthesis ) {
			return;
		}

		// Start counting variables and equals.
		$variables = 0;
		$strings   = 0;
		$error     = false;

		// Traverse all tokens before close parenthesis.
		for ( $i = $open_parenthesis; $i <= $close_parenthesis; ++$i ) {
			if ( 'T_VARIABLE' === $tokens[ $i ]['type'] ) {
				++$variables;

				if ( 0 === $strings ) {
					$error = true;
					break;
				}

				$strings = 0;
			} elseif ( 'T_STRING' === $tokens[ $i ]['type'] ) {
				++$strings;
			}
		}

		// If there are no strings before any variables, it means a variables doesn't have a type.
		if ( 0 !== $variables && true === $error ) {
			$phpcsFile->addWarningOnLine(
				'All function args must have a type.',
				$tokens[ $stackPtr ]['line'],
				'Missing',
			);
		}
	}
}
