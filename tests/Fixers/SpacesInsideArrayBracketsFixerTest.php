<?php

declare(strict_types=1);

namespace Travelopia\WordPressCodingStandards\Tests\Fixers;

use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ParseError;
use SplFileInfo;
use Travelopia\WordPressCodingStandards\Fixers\SpacesInsideArrayBracketsFixer;

final class SpacesInsideArrayBracketsFixerTest extends TestCase
{
	private SpacesInsideArrayBracketsFixer $fixer;

	protected function setUp(): void
	{
		parent::setUp();

		$this->fixer = new SpacesInsideArrayBracketsFixer();
	}

	/**
	 * Run the fixer over a snippet and return the resulting code.
	 */
	private function fix( string $code ): string
	{
		$tokens = Tokens::fromCode( $code );
		$this->fixer->fix( new SplFileInfo( __FILE__ ), $tokens );

		return $tokens->generateCode();
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function provideCases(): array
	{
		return [
			// --- String interpolation: offsets must be left EXACTLY as-is (regression). ---
			'simple interpolation, leading text, numeric offset' => [
				'<?php $x = "_$posts[0]";' . "\n",
				'<?php $x = "_$posts[0]";' . "\n",
			],
			'simple interpolation, numeric offset' => [
				'<?php $x = "$posts[0]";' . "\n",
				'<?php $x = "$posts[0]";' . "\n",
			],
			'simple interpolation, variable offset' => [
				'<?php $x = "_$posts[$i]";' . "\n",
				'<?php $x = "_$posts[$i]";' . "\n",
			],
			'simple interpolation, bareword key offset' => [
				'<?php $x = "_$posts[key]";' . "\n",
				'<?php $x = "_$posts[key]";' . "\n",
			],
			'complex interpolation, numeric offset' => [
				'<?php $x = "{$posts[0]}";' . "\n",
				'<?php $x = "{$posts[0]}";' . "\n",
			],
			'complex interpolation, variable offset' => [
				'<?php $x = "{$posts[$i]}";' . "\n",
				'<?php $x = "{$posts[$i]}";' . "\n",
			],
			'heredoc, numeric offset' => [
				"<?php \$x = <<<EOT\n_\$posts[0]\nEOT;\n",
				"<?php \$x = <<<EOT\n_\$posts[0]\nEOT;\n",
			],
			'heredoc, variable offset' => [
				"<?php \$x = <<<EOT\n_\$posts[\$i]\nEOT;\n",
				"<?php \$x = <<<EOT\n_\$posts[\$i]\nEOT;\n",
			],

			// --- Normal array access outside strings still gets WordPress spacing. ---
			'variable offset gets spaces' => [
				'<?php $x = $posts[$i];' . "\n",
				'<?php $x = $posts[ $i ];' . "\n",
			],
			'string literal offset stays unspaced' => [
				"<?php \$x = \$posts['key'];\n",
				"<?php \$x = \$posts['key'];\n",
			],
			'integer literal offset stays unspaced' => [
				'<?php $x = $posts[0];' . "\n",
				'<?php $x = $posts[0];' . "\n",
			],
			'class constant offset gets spaces' => [
				'<?php $x = $posts[ self::KEY ];' . "\n",
				'<?php $x = $posts[ self::KEY ];' . "\n",
			],
		];
	}

	#[DataProvider('provideCases')]
	public function test_fixes_code_correctly( string $input, string $expected ): void
	{
		$this->assertSame( $expected, $this->fix( $input ) );
	}

	#[DataProvider('provideCases')]
	public function test_output_is_valid_php( string $input, string $expected ): void
	{
		$output = $this->fix( $input );

		$parsed = true;
		try {
			token_get_all( $output, TOKEN_PARSE );
		} catch ( ParseError $e ) {
			$parsed = false;
		}

		$this->assertTrue( $parsed, sprintf( 'Fixer produced invalid PHP: %s', $output ) );
	}

	#[DataProvider('provideCases')]
	public function test_fixer_is_idempotent( string $input, string $expected ): void
	{
		$once  = $this->fix( $input );
		$twice = $this->fix( $once );

		$this->assertSame( $once, $twice );
	}
}
