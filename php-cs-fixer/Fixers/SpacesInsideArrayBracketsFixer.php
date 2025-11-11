<?php

namespace Travelopia\WordPressCodingStandards\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class SpacesInsideArrayBracketsFixer extends AbstractFixer
{
	public function getDefinition(): FixerDefinitionInterface
	{
		return new FixerDefinition(
			'There must be a space after opening bracket and before closing bracket of arrays.',
			[
				new CodeSample(
					"<?php\n\$array = ['foo', 'bar'];\n",
				),
			],
		);
	}

	public function isCandidate( Tokens $tokens ): bool
	{
		return $tokens->isTokenKindFound( CT::T_ARRAY_SQUARE_BRACE_OPEN )
			|| $tokens->isTokenKindFound( CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN )
			|| $tokens->isTokenKindFound( '[' );
	}

	protected function applyFix( SplFileInfo $file, Tokens $tokens ): void
	{
		for ( $index = $tokens->count() - 1; 0 <= $index; --$index ) {
			// Check for array literals.
			if ( $tokens[ $index ]->isGivenKind( CT::T_ARRAY_SQUARE_BRACE_OPEN ) ) {
				$this->fixArrayLiteralSpacing( $tokens, $index );
			}

			// Check for array access with square brackets.
			elseif ( $tokens[ $index ]->equals( '[' ) ) {
				$this->fixArrayAccessSpacing( $tokens, $index );
			}
		}
	}

	private function fixArrayLiteralSpacing( Tokens $tokens, int $index ): void
	{
		// Find the closing bracket.
		$closeIndex = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $index );

		// Check if array is empty.
		$nextIndex    = $tokens->getNextMeaningfulToken( $index );
		$isEmptyArray = $nextIndex === $closeIndex;

		if ( $isEmptyArray ) {
			// For empty arrays, ensure no spaces: []
			$tokens->ensureWhitespaceAtIndex( $index + 1, 0, '' );
			return;
		}

		// Skip multi-line arrays (check if there's a newline between brackets)
		for ( $i = $index + 1; $i < $closeIndex; ++$i ) {
			if ( $tokens[ $i ]->isGivenKind( T_WHITESPACE ) && str_contains( $tokens[ $i ]->getContent(), "\n" ) ) {
				return; // Multi-line array, skip.
			}
		}

		// Add space after opening bracket.
		if ( ! $tokens[ $index + 1 ]->isWhitespace() ) {
			$tokens->insertAt( $index + 1, new Token( [ T_WHITESPACE, ' ' ] ) );
			++$closeIndex;
		} elseif ( ' ' !== $tokens[ $index + 1 ]->getContent() ) {
			$tokens[ $index + 1 ] = new Token( [ T_WHITESPACE, ' ' ] );
		}

		// Re-find close index after potential insertions.
		$closeIndex = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $index );

		// Add space before closing bracket.
		if ( ! $tokens[ $closeIndex - 1 ]->isWhitespace() ) {
			$tokens->insertAt( $closeIndex, new Token( [ T_WHITESPACE, ' ' ] ) );
		} elseif ( ' ' !== $tokens[ $closeIndex - 1 ]->getContent() ) {
			$tokens[ $closeIndex - 1 ] = new Token( [ T_WHITESPACE, ' ' ] );
		}
	}

	private function fixArrayAccessSpacing( Tokens $tokens, int $index ): void
	{
		// Find the closing bracket for array access.
		$closeIndex = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index );

		// Check if it's empty (shouldn't happen for array access)
		$nextIndex = $tokens->getNextMeaningfulToken( $index );

		if ( $nextIndex === $closeIndex ) {
			return;
		}

		// Check if there's a concatenation operator inside the brackets.
		$hasConcatenation = false;

		for ( $i = $index + 1; $i < $closeIndex; ++$i ) {
			if ( $tokens[ $i ]->equals( '.' ) ) {
				$hasConcatenation = true;
				break;
			}
		}

		// If there's concatenation, add spaces like we do for variables.
		if ( $hasConcatenation ) {
			// Add space after opening bracket.
			if ( ! $tokens[ $index + 1 ]->isWhitespace() ) {
				$tokens->insertAt( $index + 1, new Token( [ T_WHITESPACE, ' ' ] ) );
				++$closeIndex;
			} elseif ( ' ' !== $tokens[ $index + 1 ]->getContent() ) {
				$tokens[ $index + 1 ] = new Token( [ T_WHITESPACE, ' ' ] );
			}

			// Re-find close index after potential insertions.
			$closeIndex = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index );

			// Add space before closing bracket.
			if ( ! $tokens[ $closeIndex - 1 ]->isWhitespace() ) {
				$tokens->insertAt( $closeIndex, new Token( [ T_WHITESPACE, ' ' ] ) );
			} elseif ( ' ' !== $tokens[ $closeIndex - 1 ]->getContent() ) {
				$tokens[ $closeIndex - 1 ] = new Token( [ T_WHITESPACE, ' ' ] );
			}

			return;
		}

		// Only add spaces if the index is a variable (starts with $) or a class constant (e.g., self::FIELD_NAME).

		// Check for variables or class constants.
		$isVariable = $tokens[ $nextIndex ]->isGivenKind( T_VARIABLE );

		// Check if it's a class constant (self::, static::, ClassName::).
		$isClassConstant = false;

		if ( $tokens[ $nextIndex ]->isGivenKind( [ T_STRING, T_STATIC ] ) ) {
			$afterString = $tokens->getNextMeaningfulToken( $nextIndex );

			if ( null !== $afterString && $tokens[ $afterString ]->isGivenKind( T_DOUBLE_COLON ) ) {
				$isClassConstant = true;
			}
		}

		// Check if it's a string literal (T_CONSTANT_ENCAPSED_STRING) or integer literal (T_LNUMBER).
		$isStringOrInteger = $tokens[ $nextIndex ]->isGivenKind( [ T_CONSTANT_ENCAPSED_STRING, T_LNUMBER ] );

		// If it's a string or integer literal, ensure no spaces (WordPress rule).
		if ( $isStringOrInteger ) {
			// Remove any existing spaces for string/integer literals.
			if ( $tokens[ $index + 1 ]->isWhitespace() ) {
				$tokens->clearAt( $index + 1 );
				$closeIndex = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index );
			}

			if ( $tokens[ $closeIndex - 1 ]->isWhitespace() ) {
				$tokens->clearAt( $closeIndex - 1 );
			}

			return;
		}

		// Add space after opening bracket for variables.
		if ( ! $tokens[ $index + 1 ]->isWhitespace() ) {
			$tokens->insertAt( $index + 1, new Token( [ T_WHITESPACE, ' ' ] ) );
			++$closeIndex;
		} elseif ( ' ' !== $tokens[ $index + 1 ]->getContent() ) {
			$tokens[ $index + 1 ] = new Token( [ T_WHITESPACE, ' ' ] );
		}

		// Re-find close index after potential insertions.
		$closeIndex = $tokens->findBlockEnd( Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index );

		// Add space before closing bracket for variables.
		if ( ! $tokens[ $closeIndex - 1 ]->isWhitespace() ) {
			$tokens->insertAt( $closeIndex, new Token( [ T_WHITESPACE, ' ' ] ) );
		} elseif ( ' ' !== $tokens[ $closeIndex - 1 ]->getContent() ) {
			$tokens[ $closeIndex - 1 ] = new Token( [ T_WHITESPACE, ' ' ] );
		}
	}

	public function getName(): string
	{
		return 'Travelopia/spaces_inside_array_brackets';
	}

	public function getPriority(): int
	{
		// Should run after array_syntax.
		return 0;
	}
}
