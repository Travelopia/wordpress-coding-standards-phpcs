<?php

namespace Travelopia\WordPressCodingStandards\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class BlankLineBeforeCommentFixer extends AbstractFixer
{
	public function getDefinition(): FixerDefinitionInterface
	{
		return new FixerDefinition(
			'There must be a blank line before comments, unless it is the first line in a block.',
			[
				new CodeSample(
					"<?php\nfunction foo() {\n\$a = 1;\n// Comment\n\$b = 2;\n}\n",
				),
			],
		);
	}

	public function isCandidate( Tokens $tokens ): bool
	{
		return $tokens->isTokenKindFound( T_COMMENT ) || $tokens->isTokenKindFound( T_DOC_COMMENT );
	}

	protected function applyFix( SplFileInfo $file, Tokens $tokens ): void
	{
		// First, handle the opening tag case.
		$this->handleOpeningTag( $tokens );

		for ( $index = 0; $index < $tokens->count(); ++$index ) {
			if ( ! $tokens[ $index ]->isComment() ) {
				continue;
			}

			// Skip if this is a DocBlock comment (those have their own rules)
			if ( $tokens[ $index ]->isGivenKind( T_DOC_COMMENT ) ) {
				continue;
			}

			$this->ensureBlankLineBeforeComment( $tokens, $index );
		}
	}

	private function handleOpeningTag( Tokens $tokens ): void
	{
		// Find the opening PHP tag (usually index 0).
		if ( ! $tokens[0]->isGivenKind( T_OPEN_TAG ) ) {
			return;
		}

		// Check the next token.
		if ( ! isset( $tokens[1] ) ) {
			return;
		}

		// If the next token is not whitespace, nothing to do.
		if ( ! $tokens[1]->isWhitespace() ) {
			return;
		}

		// Get the token after the whitespace.
		$tokenAfterWhitespace = $tokens->getNextNonWhitespace( 1 );

		if ( null === $tokenAfterWhitespace ) {
			return;
		}

		$whitespace   = $tokens[1]->getContent();
		$newlineCount = substr_count( $whitespace, "\n" );

		// If there's a docblock after the opening tag, ensure NO blank line.
		if ( $tokens[ $tokenAfterWhitespace ]->isGivenKind( T_DOC_COMMENT ) ) {
			if ( 1 < $newlineCount ) {
				// Remove extra blank lines - just one newline.
				$tokens[1] = new Token( [ T_WHITESPACE, "\n" ] );
			}
		} else {
			// If there's NO docblock, ensure there IS a blank line.
			if ( 1 === $newlineCount ) {
				// Add a blank line.
				$tokens[1] = new Token( [ T_WHITESPACE, "\n\n" ] );
			}
		}
	}

	private function ensureBlankLineBeforeComment( Tokens $tokens, int $index ): void
	{
		// Find the previous non-whitespace token.
		$prevIndex = $tokens->getPrevNonWhitespace( $index );

		if ( null === $prevIndex ) {
			return; // Comment is at the start of the file.
		}

		// Check if the previous token is the opening PHP tag (file-level docblock).
		if ( $tokens[ $prevIndex ]->isGivenKind( T_OPEN_TAG ) ) {
			return; // This is a file-level docblock, skip.
		}

		// Check if the previous token is an opening brace (start of a block)
		if ( $tokens[ $prevIndex ]->equals( '{' ) ) {
			return; // Comment is the first line in a block.
		}

		// Check if the comment is inside a function call (within parentheses).
		if ( $this->isInsideFunctionCall( $tokens, $index ) ) {
			return; // Comment is inside a function call, skip.
		}

		// Check the whitespace between the previous token and the comment.
		$whitespaceIndex = $index - 1;

		// Make sure there's whitespace before the comment.
		if ( ! $tokens[ $whitespaceIndex ]->isWhitespace() ) {
			return; // No whitespace, shouldn't happen but skip.
		}

		$whitespace = $tokens[ $whitespaceIndex ]->getContent();

		// Count newlines in the whitespace.
		$newlineCount = substr_count( $whitespace, "\n" );

		// If there's only one newline, we need to add another one.
		if ( 1 === $newlineCount ) {
			// Extract the indentation from the last line.
			$parts       = explode( "\n", $whitespace );
			$indentation = end( $parts );

			// Add a blank line (two newlines with indentation)
			$tokens[ $whitespaceIndex ] = new Token( [ T_WHITESPACE, "\n\n" . $indentation ] );
		}
	}

	private function isInsideFunctionCall( Tokens $tokens, int $index ): bool
	{
		$depth = 0;

		// Walk backwards from the comment.
		for ( $i = $index - 1; 0 <= $i; --$i ) {
			$token = $tokens[ $i ];

			// If we encounter a closing parenthesis, increment depth.
			if ( $token->equals( ')' ) ) {
				++$depth;
			}

			// If we encounter an opening parenthesis, decrement depth.
			if ( $token->equals( '(' ) ) {
				--$depth;

				// If depth is negative, we found an opening paren without a matching closing paren.

				// This means the comment is inside a function call.
				if ( 0 > $depth ) {
					return true;
				}
			}

			// If we encounter a semicolon or opening brace at depth 0, we're not in a function call.
			if ( 0 === $depth && ( $token->equals( ';' ) || $token->equals( '{' ) ) ) {
				return false;
			}
		}

		return false;
	}

	public function getName(): string
	{
		return 'Travelopia/blank_line_before_comment';
	}

	public function getPriority(): int
	{
		// Should run after indentation and other formatting.
		return -10;
	}
}
