<?php
/**
 * Sniff: NoLeadingSlashOnUseSniff.
 *
 * @package travelopia-coding-standards
 */

namespace Travelopia\Sniffs\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check `use` class isn't prefixed with `\`
 */
class NoLeadingSlashOnUseSniff implements Sniff {

	/**
	 * Register the sniff.
	 *
	 * @return mixed[]
	 */
	public function register(): array {
		return [ T_USE ];
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
		$tokens   = $phpcsFile->getTokens();
		$look_for = [ T_STRING, T_NS_SEPARATOR ];
		$next     = $phpcsFile->findNext( $look_for, $stackPtr );
		if ( T_NS_SEPARATOR === $tokens[ $next ]['code'] ) {
			$name = '';
			do {
				$next ++;
				$name .= $tokens[ $next ]['content'];
			} while ( in_array( $tokens[ $next + 1 ]['code'], $look_for, true ) );

			$error = '`use` statement for class %s should not prefix with a backslash';

			$phpcsFile->addWarning( $error, $stackPtr, 'LeadingSlash', [ $name ] );
		}
	}
}
