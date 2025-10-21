<?php

namespace Travelopia\WordPressCodingStandards\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class CommentPunctuationFixer extends AbstractFixer
{
	public function getDefinition(): FixerDefinitionInterface
	{
		return new FixerDefinition(
			'Inline comments must end with a period or special character.',
			[
				new CodeSample(
					"<?php\n// This is a comment\n\$a = 1;\n",
				),
			],
		);
	}

	public function isCandidate( Tokens $tokens ): bool
	{
		return $tokens->isTokenKindFound( T_COMMENT );
	}

	protected function applyFix( SplFileInfo $file, Tokens $tokens ): void
	{
		for ( $index = 0; $index < $tokens->count(); ++$index ) {
			if ( ! $tokens[ $index ]->isGivenKind( T_COMMENT ) ) {
				continue;
			}

			// Skip DocBlock comments.
			if ( $tokens[ $index ]->isGivenKind( T_DOC_COMMENT ) ) {
				continue;
			}

			$this->ensureCommentEndsWithPunctuation( $tokens, $index );
		}
	}

	private function ensureCommentEndsWithPunctuation( Tokens $tokens, int $index ): void
	{
		$content = $tokens[ $index ]->getContent();

		// Skip multi-line comments (/* ... */)
		if ( str_starts_with( $content, '/*' ) ) {
			return;
		}

		// Handle single-line comments (// or #)
		$commentText = $content;

		// Remove the comment marker and trim.
		if ( str_starts_with( $commentText, '//' ) ) {
			$commentText = substr( $commentText, 2 );
		} elseif ( str_starts_with( $commentText, '#' ) ) {
			$commentText = substr( $commentText, 1 );
		}

		// Trim whitespace but keep the newline at the end if it exists.
		$hasNewline  = str_ends_with( $commentText, "\n" );
		$commentText = rtrim( $commentText );

		// Skip empty comments.
		if ( empty( $commentText ) ) {
			return;
		}

		// Check if the comment already ends with punctuation or special characters.
		$lastChar = mb_substr( $commentText, -1 );

		$punctuationChars = [ '.', '!', '?', ':', ';', ',', ')', ']', '}', '>', '"', "'", '`' ];

		if ( in_array( $lastChar, $punctuationChars, true ) ) {
			return; // Already has punctuation.
		}

		// Add a period.
		if ( str_starts_with( $content, '//' ) ) {
			$newContent = '//' . rtrim( substr( $content, 2 ) ) . '.';
		} elseif ( str_starts_with( $content, '#' ) ) {
			$newContent = '#' . rtrim( substr( $content, 1 ) ) . '.';
		} else {
			return;
		}

		// Restore newline if it existed.
		if ( $hasNewline ) {
			$newContent .= "\n";
		}

		$tokens[ $index ] = new Token( [ T_COMMENT, $newContent ] );
	}

	public function getName(): string
	{
		return 'Travelopia/comment_punctuation';
	}

	public function getPriority(): int
	{
		// Should run after other comment formatting.
		return -20;
	}
}
