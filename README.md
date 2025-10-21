# Travelopia WordPress PHP Coding Standards

![maintenance-status](https://img.shields.io/badge/maintenance-actively--developed-brightgreen.svg)

Official Travelopia WordPress PHP coding standards.

<table width="100%">
	<tr>
		<td align="left" width="70%">
            <p>Built by the super talented team at <strong><a href="https://www.travelopia.com/work-with-us/">Travelopia</a></strong>.</p>
		</td>
		<td align="center" width="30%">
			<img src="https://www.travelopia.com/wp-content/themes/travelopia/assets/svg/logo-travelopia-circle.svg" width="50" />
		</td>
	</tr>
</table>

## Installation
Install the library via Composer:

```
$ composer require --dev travelopia/wordpress-coding-standards
```

That's it!

## Usage

### PHP_CodeSniffer (PHPCS)
Lint your PHP files with the following command:

```bash
$ ./vendor/bin/phpcs .
```

### PHP-CS-Fixer

This package also includes custom PHP-CS-Fixer rules for automated code formatting.

#### Quick Start

Create a `.php-cs-fixer.dist.php` file in your project root:

```php
<?php
use PhpCsFixer\Finder;
use Travelopia\WordPressCodingStandards\TravelopiaFixersConfig;

$finder = Finder::create()
    ->in( __DIR__ )
    ->name( '*.php' )
    ->exclude( 'vendor' )
    ->ignoreVCS( true );

$config = TravelopiaFixersConfig::create()
    ->setRiskyAllowed( true )
    ->setIndent( "\t" )
    ->setLineEnding( "\n" )
    ->setRules( TravelopiaFixersConfig::getRules() )
    ->setFinder( $finder );

return $config;
```

#### Run PHP-CS-Fixer

```bash
# Check for issues
$ ./vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix issues
$ ./vendor/bin/php-cs-fixer fix
```
