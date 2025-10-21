<?php

namespace Travelopia\WordPressCodingStandards\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class DocBlockOnNewLineFixer extends AbstractFixer
{
	public function getDefinition(): FixerDefinitionInterface
	{
		return new FixerDefinition(
			'DocBlock comments must start on a new line, not inline with code.',
			[
				new CodeSample(
					"<?php\n\$array = [\n\t'key' => 'value', /**\n\t * DocBlock\n\t */\n\t'resolve' => function() {},\n];\n",
				),
			],
		);
	}

	public function isCandidate( Tokens $tokens ): bool
	{
		return $tokens->isTokenKindFound( T_DOC_COMMENT );
	}

	protected function applyFix( SplFileInfo $file, Tokens $tokens ): void
	{
		for ( $index = 0; $index < $tokens->count(); ++$index ) {
			if ( ! $tokens[ $index ]->isGivenKind( T_DOC_COMMENT ) ) {
				continue;
			}

			// Check if there's non-whitespace content on the same line before the docblock.
			$this->ensureDocBlockOnNewLine( $tokens, $index );
		}
	}

	private function ensureDocBlockOnNewLine( Tokens $tokens, int $index ): void
	{
		// Look at the previous token.
		$prevIndex = $index - 1;

		if ( 0 > $prevIndex ) {
			return;
		}

		// If the previous token is whitespace, check if it contains a newline.
		if ( $tokens[ $prevIndex ]->isWhitespace() ) {
			$whitespace = $tokens[ $prevIndex ]->getContent();

			// If there's a newline, the docblock is already on a new line.
			if ( str_contains( $whitespace, "\n" ) ) {
				return;
			}

			// Otherwise, we need to find what's before this whitespace.
			$prevNonWhitespace = $tokens->getPrevNonWhitespace( $prevIndex );

			if ( null === $prevNonWhitespace ) {
				return;
			}

			// Check if this is right after opening tag.
			if ( $tokens[ $prevNonWhitespace ]->isGivenKind( T_OPEN_TAG ) ) {
				return;
			}

			// Get proper indentation by looking at the line context.
			$indentation = $this->getProperIndentation( $tokens, $index );

			// Replace the whitespace with a newline + indentation.
			$tokens[ $prevIndex ] = new Token( [ T_WHITESPACE, "\n" . $indentation ] );

			return;
		}

		// If previous token is not whitespace, we need to insert whitespace with a newline.
		$prevNonWhitespace = $prevIndex;

		// Check if this is right after opening tag.
		if ( $tokens[ $prevNonWhitespace ]->isGivenKind( T_OPEN_TAG ) ) {
			return;
		}

		// Get proper indentation by looking at the line context.
		$indentation = $this->getProperIndentation( $tokens, $index );

		// Insert a whitespace token with newline + indentation before the docblock.
		$tokens->insertAt( $index, new Token( [ T_WHITESPACE, "\n" . $indentation ] ) );
	}

	private function getProperIndentation( Tokens $tokens, int $docBlockIndex ): string
	{
		// Look for the previous non-whitespace, non-comment token to understand context.
		$prevNonWhitespace = $tokens->getPrevNonWhitespace( $docBlockIndex - 1 );

		if ( null === $prevNonWhitespace ) {
			return '';
		}

		// Find the start of the line for the previous token.
		$lineStart = $prevNonWhitespace;

		while ( 0 < $lineStart ) {
			$prev = $lineStart - 1;

			if ( $tokens[ $prev ]->isWhitespace() ) {
				$content = $tokens[ $prev ]->getContent();

				if ( str_contains( $content, "\n" ) ) {
					// Extract indentation after the last newline.
					$parts = explode( "\n", $content );

					return end( $parts );
				}
			}

			--$lineStart;
		}

		return '';
	}

	public function getName(): string
	{
		return 'Travelopia/docblock_on_new_line';
	}

	public function getPriority(): int
	{
		// Should run after other formatting but before alignment.
		return -5;
	}
}
