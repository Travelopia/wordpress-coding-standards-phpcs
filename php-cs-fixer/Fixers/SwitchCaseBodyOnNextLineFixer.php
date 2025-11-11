<?php

namespace Travelopia\WordPressCodingStandards\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

/**
 * Fixer to ensure CASE and DEFAULT body starts on the line following the statement.
 * This complies with PSR2.ControlStructures.SwitchDeclaration.BodyOnNextLineCASE.
 */
final class SwitchCaseBodyOnNextLineFixer extends AbstractFixer
{
	public function getDefinition(): FixerDefinitionInterface
	{
		return new FixerDefinition(
			'The CASE and DEFAULT body must start on the line following the statement (PSR2.ControlStructures.SwitchDeclaration.BodyOnNextLineCASE).',
			[
				new CodeSample(
					"<?php\nswitch ( \$var ) {\n\tcase 'value':\n\n\t\t\$a = 1;\n\t\tbreak;\n}\n",
				),
			],
		);
	}

	public function isCandidate( Tokens $tokens ): bool
	{
		return $tokens->isTokenKindFound( T_SWITCH )
			|| $tokens->isTokenKindFound( T_CASE )
			|| $tokens->isTokenKindFound( T_DEFAULT );
	}

	protected function applyFix( SplFileInfo $file, Tokens $tokens ): void
	{
		for ( $index = $tokens->count() - 1; 0 <= $index; --$index ) {
			// Check for case statements.
			if ( $tokens[ $index ]->isGivenKind( T_CASE ) ) {
				$this->fixCaseBody( $tokens, $index );
			}

			// Check for default statements.
			if ( $tokens[ $index ]->isGivenKind( T_DEFAULT ) ) {
				$this->fixDefaultBody( $tokens, $index );
			}
		}
	}

	/**
	 * Fix case statement to ensure body starts on next line.
	 */
	private function fixCaseBody( Tokens $tokens, int $caseIndex ): void
	{
		// Find the colon after the case value.
		$colonIndex = $tokens->getNextTokenOfKind( $caseIndex, [ ':' ] );

		if ( null === $colonIndex ) {
			return;
		}

		// Get the next token after the colon.
		$nextIndex = $colonIndex + 1;

		if ( ! isset( $tokens[ $nextIndex ] ) ) {
			return;
		}

		// If the next token is whitespace, check if it has multiple newlines.
		if ( $tokens[ $nextIndex ]->isWhitespace() ) {
			$whitespace = $tokens[ $nextIndex ]->getContent();
			$newlineCount = substr_count( $whitespace, "\n" );

			// If there are 2 or more newlines (meaning a blank line), remove the extra ones.
			if ( $newlineCount >= 2 ) {
				// Extract the indentation from the last line (the line where code should start).
				$parts = explode( "\n", $whitespace );
				$indentation = end( $parts );

				// Replace with single newline + indentation.
				$tokens[ $nextIndex ] = new Token( [ T_WHITESPACE, "\n" . $indentation ] );
			}
		}
	}

	/**
	 * Fix default statement to ensure body starts on next line.
	 */
	private function fixDefaultBody( Tokens $tokens, int $defaultIndex ): void
	{
		// Find the colon after default.
		$colonIndex = $tokens->getNextTokenOfKind( $defaultIndex, [ ':' ] );

		if ( null === $colonIndex ) {
			return;
		}

		// Get the next token after the colon.
		$nextIndex = $colonIndex + 1;

		if ( ! isset( $tokens[ $nextIndex ] ) ) {
			return;
		}

		// If the next token is whitespace, check if it has multiple newlines.
		if ( $tokens[ $nextIndex ]->isWhitespace() ) {
			$whitespace = $tokens[ $nextIndex ]->getContent();
			$newlineCount = substr_count( $whitespace, "\n" );

			// If there are 2 or more newlines (meaning a blank line), remove the extra ones.
			if ( $newlineCount >= 2 ) {
				// Extract the indentation from the last line (the line where code should start).
				$parts = explode( "\n", $whitespace );
				$indentation = end( $parts );

				// Replace with single newline + indentation.
				$tokens[ $nextIndex ] = new Token( [ T_WHITESPACE, "\n" . $indentation ] );
			}
		}
	}

	public function getName(): string
	{
		return 'Travelopia/switch_case_body_on_next_line';
	}

	public function getPriority(): int
	{
		// Should run after other formatting rules but before blank line rules.
		return -10;
	}
}
