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
		// Never touch array access inside string interpolation ("$a[0]", "$a[$i]", "$a[key]").
		// PHP tokenizes the offset differently there and inserting spaces produces a fatal
		// parse error in the simple-interpolation syntax (e.g. "$a[ 0 ]").
		if ( $this->isStringInterpolationAccess( $tokens, $index ) ) {
			return;
		}

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

	/**
	 * Detect whether a '[' opens array access inside a double-quoted string, heredoc,
	 * or backtick expression (string interpolation). Offsets there must be left exactly
	 * as-is: PHP tokenizes them differently and spaces are either a fatal parse error
	 * (simple syntax: "$a[0]") or unwanted noise (complex syntax: "{$a[$i]}").
	 */
	private function isStringInterpolationAccess( Tokens $tokens, int $index ): bool
	{
		// The offset token sits directly after the '['.
		$offsetIndex = $index + 1;

		// T_NUM_STRING only ever appears as an array offset inside simple string
		// interpolation (e.g. "$a[0]"), so it is a definitive signal on its own.
		if ( isset( $tokens[ $offsetIndex ] ) && $tokens[ $offsetIndex ]->isGivenKind( T_NUM_STRING ) ) {
			return true;
		}

		// For "$a[$i]" and "$a[key]" the offset is T_VARIABLE / T_STRING, which also occur
		// in normal code. Disambiguate via the base being indexed: in interpolation the '['
		// is preceded by a single T_VARIABLE that is itself preceded by a string-boundary
		// or interpolation-opening token. Those tokens never precede an indexed variable in
		// ordinary PHP, so there is no risk of a false positive.
		$baseIndex = $index - 1;

		if ( ! isset( $tokens[ $baseIndex ] ) || ! $tokens[ $baseIndex ]->isGivenKind( T_VARIABLE ) ) {
			return false;
		}

		$beforeBaseIndex = $baseIndex - 1;

		if ( ! isset( $tokens[ $beforeBaseIndex ] ) ) {
			return false;
		}

		$beforeBase = $tokens[ $beforeBaseIndex ];

		// Interpolation-context markers that can immediately precede the base variable:
		// - T_ENCAPSED_AND_WHITESPACE: literal text before the variable ("_$a[..]").
		// - T_START_HEREDOC: the variable is the first thing in a heredoc/nowdoc body.
		// - T_CURLY_OPEN / T_DOLLAR_OPEN_CURLY_BRACES: complex syntax ("{$a[..]}", "${a[..]}").
		if ( $beforeBase->isGivenKind( [ T_ENCAPSED_AND_WHITESPACE, T_START_HEREDOC, T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES ] ) ) {
			return true;
		}

		// The variable is the first content of a double-quoted or backtick string ("$a[..]").
		if ( $beforeBase->equalsAny( [ '"', '`' ] ) ) {
			return true;
		}

		return false;
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
