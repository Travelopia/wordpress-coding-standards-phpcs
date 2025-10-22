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
			if ( ! $tokens[ $index ]->isGivenKind( [ T_COMMENT, T_DOC_COMMENT ] ) ) {
				continue;
			}

			if ( $tokens[ $index ]->isGivenKind( T_DOC_COMMENT ) ) {
				$this->fixDocBlockComment( $tokens, $index );
			} else {
				$this->ensureCommentEndsWithPunctuation( $tokens, $index );
			}
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

		// Skip PHPCS and PHPStan directives (phpcs:ignore, phpstan-ignore-line, etc.)
		if ( preg_match( '/(phpcs|phpstan):[a-z-]+$/i', $commentText ) ) {
			return;
		}

		// Skip comments ending with version numbers (e.g., "Version: 1.0.0")
		if ( preg_match( '/\d+\.\d+(\.\d+)?$/i', $commentText ) ) {
			return;
		}

		// Skip WordPress template headers and plugin/theme metadata (e.g., "Template Name: Components")
		if ( preg_match( '/^(Template Name|Template Post Type|Plugin Name|Theme Name|Author|Version|Description|Text Domain|Domain Path|Requires at least|Requires PHP|License|License URI|Tags):.+$/i', $commentText ) ) {
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

	private function fixDocBlockComment( Tokens $tokens, int $index ): void
	{
		$content = $tokens[ $index ]->getContent();

		// Check if this DocBlock contains WordPress template headers.
		if ( preg_match( '/\* (Template Name|Template Post Type|Plugin Name|Theme Name|Author|Version|Description|Text Domain|Domain Path|Requires at least|Requires PHP|License|License URI|Tags):[^\n]+\.\s*$/im', $content ) ) {
			// Remove trailing period from WordPress headers.
			$newContent = preg_replace( '/(\* (?:Template Name|Template Post Type|Plugin Name|Theme Name|Author|Version|Description|Text Domain|Domain Path|Requires at least|Requires PHP|License|License URI|Tags):[^\n]+)\.\s*$/im', '$1', $content );
			$tokens[ $index ] = new Token( [ T_DOC_COMMENT, $newContent ] );
		}
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
