<?php

namespace Travelopia\WordPressCodingStandards\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class BlankLineAfterControlStructureFixer extends AbstractFixer
{
	public function getDefinition(): FixerDefinitionInterface
	{
		return new FixerDefinition(
			'There must be a blank line after control structures (if, for, foreach, while, switch, try).',
			[
				new CodeSample(
					"<?php\nif ( true ) {\n\$a = 1;\n}\n\$b = 2;\n",
				),
			],
		);
	}

	public function isCandidate( Tokens $tokens ): bool
	{
		return $tokens->isAnyTokenKindsFound( [ T_IF, T_FOR, T_FOREACH, T_WHILE, T_SWITCH, T_DO, T_TRY ] );
	}

	protected function applyFix( SplFileInfo $file, Tokens $tokens ): void
	{
		for ( $index = $tokens->count() - 1; 0 <= $index; --$index ) {
			if ( ! $tokens[ $index ]->isGivenKind( [ T_IF, T_FOR, T_FOREACH, T_WHILE, T_SWITCH, T_DO, T_TRY ] ) ) {
				continue;
			}

			$this->ensureBlankLineAfterControlStructure( $tokens, $index );
		}
	}

	private function ensureBlankLineAfterControlStructure( Tokens $tokens, int $index ): void
	{
		// Find the closing brace for this control structure.
		$openBraceIndex = $tokens->getNextTokenOfKind( $index, [ '{' ] );

		if ( null === $openBraceIndex ) {
			return; // No opening brace found (single-line statement).
		}

		$closeBraceIndex = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_CURLY_BRACE, $openBraceIndex );

		if ( null === $closeBraceIndex ) {
			return;
		}

		// Special case for do-while: the closing brace is followed by 'while'.
		if ( $tokens[ $index ]->isGivenKind( T_DO ) ) {
			// Find the semicolon after 'while'.
			$whileIndex = $tokens->getNextMeaningfulToken( $closeBraceIndex );

			if ( null !== $whileIndex && $tokens[ $whileIndex ]->isGivenKind( T_WHILE ) ) {
				$semicolonIndex = $tokens->getNextTokenOfKind( $whileIndex, [ ';' ] );

				if ( null !== $semicolonIndex ) {
					$closeBraceIndex = $semicolonIndex;
				}
			}
		}

		// Special case for try-catch: check if there's a 'catch' or 'finally' after.
		if ( $tokens[ $index ]->isGivenKind( T_TRY ) ) {
			$nextMeaningful = $tokens->getNextMeaningfulToken( $closeBraceIndex );

			if ( null !== $nextMeaningful && $tokens[ $nextMeaningful ]->isGivenKind( [ T_CATCH, T_FINALLY ] ) ) {
				// Find the last catch/finally block.
				$lastBlockIndex = $this->findLastCatchOrFinally( $tokens, $nextMeaningful );

				if ( null !== $lastBlockIndex ) {
					$closeBraceIndex = $lastBlockIndex;
				}
			}
		}

		// Special case for if-elseif-else: find the last else block.
		if ( $tokens[ $index ]->isGivenKind( T_IF ) ) {
			$lastElseIndex = $this->findLastElse( $tokens, $closeBraceIndex );

			if ( null !== $lastElseIndex ) {
				$closeBraceIndex = $lastElseIndex;
			}
		}

		// Get the next token after the closing brace.
		$nextIndex = $closeBraceIndex + 1;

		if ( ! isset( $tokens[ $nextIndex ] ) ) {
			return; // No token after the closing brace.
		}

		// If the next token is not whitespace, we need to add it.
		if ( ! $tokens[ $nextIndex ]->isWhitespace() ) {
			$tokens->insertAt( $nextIndex, new Token( [ T_WHITESPACE, "\n\n" ] ) );

			return;
		}

		// Check if we should add a blank line.
		$nextNonWhitespace = $tokens->getNextNonWhitespace( $closeBraceIndex );

		if ( null === $nextNonWhitespace ) {
			return; // End of file.
		}

		// Don't add blank line before closing braces or elseif/else/catch/finally.
		if ( $tokens[ $nextNonWhitespace ]->equals( '}' )
			|| $tokens[ $nextNonWhitespace ]->isGivenKind( [ T_ELSEIF, T_ELSE, T_CATCH, T_FINALLY ] ) ) {
			return;
		}

		// Get the whitespace content.
		$whitespace = $tokens[ $nextIndex ]->getContent();

		// Count newlines in the whitespace.
		$newlineCount = substr_count( $whitespace, "\n" );

		// If there's only one newline, we need to add another one.
		if ( 1 === $newlineCount ) {
			// Extract the indentation from the last line.
			$parts       = explode( "\n", $whitespace );
			$indentation = end( $parts );

			// Add a blank line.
			$tokens[ $nextIndex ] = new Token( [ T_WHITESPACE, "\n\n" . $indentation ] );
		}
	}

	private function findLastElse( Tokens $tokens, int $closeBraceIndex ): ?int
	{
		$currentIndex = $closeBraceIndex;

		while ( true ) {
			$nextMeaningful = $tokens->getNextMeaningfulToken( $currentIndex );

			if ( null === $nextMeaningful ) {
				return $currentIndex;
			}

			if ( ! $tokens[ $nextMeaningful ]->isGivenKind( [ T_ELSEIF, T_ELSE ] ) ) {
				return $currentIndex;
			}

			// Find the opening brace for this elseif/else.
			$openBrace = $tokens->getNextTokenOfKind( $nextMeaningful, [ '{' ] );

			if ( null === $openBrace ) {
				return $currentIndex;
			}

			// Find the closing brace.
			$closeBrace = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_CURLY_BRACE, $openBrace );

			if ( null === $closeBrace ) {
				return $currentIndex;
			}

			$currentIndex = $closeBrace;
		}
	}

	private function findLastCatchOrFinally( Tokens $tokens, int $startIndex ): ?int
	{
		$currentIndex = $startIndex;

		while ( true ) {
			if ( ! $tokens[ $currentIndex ]->isGivenKind( [ T_CATCH, T_FINALLY ] ) ) {
				break;
			}

			// Find the opening brace for this catch/finally.
			$openBrace = $tokens->getNextTokenOfKind( $currentIndex, [ '{' ] );

			if ( null === $openBrace ) {
				break;
			}

			// Find the closing brace.
			$closeBrace = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_CURLY_BRACE, $openBrace );

			if ( null === $closeBrace ) {
				break;
			}

			$lastCloseBrace = $closeBrace;

			// Check if there's another catch/finally after this.
			$nextMeaningful = $tokens->getNextMeaningfulToken( $closeBrace );

			if ( null === $nextMeaningful || ! $tokens[ $nextMeaningful ]->isGivenKind( [ T_CATCH, T_FINALLY ] ) ) {
				return $lastCloseBrace;
			}

			$currentIndex = $nextMeaningful;
		}

		return null;
	}

	public function getName(): string
	{
		return 'Travelopia/blank_line_after_control_structure';
	}

	public function getPriority(): int
	{
		// Should run after indentation and other formatting.
		return -20;
	}
}
