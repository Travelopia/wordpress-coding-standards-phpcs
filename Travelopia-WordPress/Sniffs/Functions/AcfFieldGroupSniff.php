<?php
/**
 * Sniff: AcfFieldGroupSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniffs to check for ACF Field Groups.
 */
class AcfFieldGroupSniff implements Sniff
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

		// Check for ACF group function.
		if ( 'acf_add_local_field_group' !== $tokens[ $stackPtr ]['content'] ) {
			return;
		}

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

		// Prepare tokens.
		$style_token    = [];
		$seamless_token = [];

		// Traverse all tokens before close parenthesis.
		for ( $i = $open_parenthesis; $i <= $close_parenthesis; ++$i  ) {
			// Look for encapsed strings.
			if ( T_CONSTANT_ENCAPSED_STRING !== $tokens[ $i ]['code'] ) {
				continue;
			}

			// Look for seamless style.
			if ( "'seamless'" === $tokens[ $i ]['content'] ) {
				if ( ! empty( $style_token ) && $style_token['line'] === $tokens[ $i ]['line'] ) {
					$seamless_token = $tokens[ $i ];
					break;
				}
			} elseif ( "'style'" === $tokens[ $i ]['content'] ) {
				$style_token = $tokens[ $i ];
			}
		}

		// Show an error if no seamless style found.
		if ( empty( $seamless_token ) ) {
			$phpcsFile->addWarningOnLine(
				'ACF field groups must have "seamless" style.',
				$tokens[ $stackPtr ]['line'],
				'MissingSeamless',
			);
		}
	}
}
