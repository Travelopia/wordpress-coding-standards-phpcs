<?php
/**
 * Sniff: WPQueryParamsSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Classes;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniffs related to WP_Query params.
 */
class WPQueryParamsSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_CONSTANT_ENCAPSED_STRING ];
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

		if ( 'post__not_in' === trim( $tokens[ $stackPtr ]['content'], '\'' ) ) {
			$phpcsFile->addWarning( 'Using `post__not_in` should be done with caution.', $stackPtr, 'post__not_in' );
		}
	}

}
