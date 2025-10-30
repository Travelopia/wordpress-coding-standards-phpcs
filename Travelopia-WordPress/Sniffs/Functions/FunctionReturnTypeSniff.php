<?php
/**
 * Sniff: FunctionReturnTypeSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

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

		// Find the closing parenthesis of the function parameters.
		$open_parenthesis = $phpcsFile->findNext( [ T_OPEN_PARENTHESIS ], $stackPtr );

		if ( false === $open_parenthesis || ! isset( $tokens[ $open_parenthesis ]['parenthesis_closer'] ) ) {
			return;
		}

		$close_parenthesis = $tokens[ $open_parenthesis ]['parenthesis_closer'];

		// Find the opening brace of the function body (or semicolon for abstract/interface methods).
		$scope_opener = $phpcsFile->findNext( array_merge( Tokens::$scopeOpeners, [ T_SEMICOLON ] ), $close_parenthesis );

		if ( false === $scope_opener ) {
			return;
		}

		// Check if there's a colon between the closing parenthesis and the scope opener.
		$return_type_colon = $phpcsFile->findNext( [ T_COLON ], $close_parenthesis, $scope_opener );

		if ( false === $return_type_colon ) {
			$phpcsFile->addWarningOnLine(
				'Functions must have a return type.',
				$tokens[ $stackPtr ]['line'],
				'Missing',
			);
		}
	}
}
