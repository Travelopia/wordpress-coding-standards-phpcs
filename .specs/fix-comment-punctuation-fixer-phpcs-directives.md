# Fix: CommentPunctuationFixer incorrectly mutates `phpcs:disable` directives with sniff names

## Background

`CommentPunctuationFixer` enforces that all inline PHP comments end with a punctuation character.
It has a guard to skip PHPCS and PHPStan directive comments so they are left untouched.

## Problem

The guard regex was:

```php
if ( preg_match( '/(phpcs|phpstan):[a-z-]+$/i', $commentText ) ) {
    return;
}
```

The `$` anchor requires the match to be at the **end** of the string, and `[a-z-]+` only matches
lowercase letters and hyphens. This works for bare directives:

```php
// phpcs:ignore              ✅ regex matches "phpcs:ignore"      → skipped correctly
// phpcs:disable             ✅ regex matches "phpcs:disable"     → skipped correctly
// phpstan-ignore-next-line  ✅ regex matches at end              → skipped correctly
```

But when sniff names follow the directive keyword, the end-of-string no longer matches `[a-z-]+`
because sniff names contain uppercase letters, dots, and commas:

```php
// phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing,Travelopia.Functions.FunctionArgsTypes.Missing
```

The regex fails to match → the fixer falls through → appends `.` → the comment becomes:

```php
// phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing,Travelopia.Functions.FunctionArgsTypes.Missing.
```

The trailing `.` is now part of the last sniff name token. PHPCS does not recognise
`Travelopia.Functions.FunctionArgsTypes.Missing.` as a valid sniff code, so the directive silently
stops suppressing the warning it was written to suppress.

## Real-world impact (discovered in hayesandjarvis)

`Sitemap_Provider` implements the Yoast `WPSEO_Sitemap_Provider` interface. That interface declares
its methods without parameter type hints. PHP does not allow the implementing class to add type
hints (it narrows the accepted type, violating LSP), so the class must omit them too. PHPCS flags
missing type hints with two sniffs:

- `Squiz.Commenting.FunctionComment.ScalarTypeHintMissing`
- `Travelopia.Functions.FunctionArgsTypes.Missing`

The correct suppression is a file-level `phpcs:disable`:

```php
// phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing,Travelopia.Functions.FunctionArgsTypes.Missing
```

After running `format:php:fix`, the fixer mutated this to:

```php
// phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing,Travelopia.Functions.FunctionArgsTypes.Missing.
```

`lint:php` then reported the warnings again because the disable directive was broken.

## Fix

Anchor the match to the **start** of the comment text (after stripping `//`) and drop the
suffix pattern entirely — any comment that begins with `phpcs:` or `phpstan:` is a tool directive
and must not be modified:

```php
// Before
if ( preg_match( '/(phpcs|phpstan):[a-z-]+$/i', $commentText ) ) {

// After
if ( preg_match( '/^\s*(phpcs|phpstan):/i', $commentText ) ) {
```

## Before / after examples

| Comment | Before fix | After fix |
|---|---|---|
| `// phpcs:ignore` | unchanged | unchanged |
| `// phpcs:disable` | unchanged | unchanged |
| `// phpcs:enable` | unchanged | unchanged |
| `// phpcs:disable Squiz.Commenting.ScalarTypeHintMissing` | `.` appended — directive broken | unchanged |
| `// phpcs:disable SniffA,SniffB,SniffC` | `.` appended — directive broken | unchanged |
| `// phpcs:ignore SniffA -- reason` | `.` appended | unchanged |
| `// phpstan-ignore-next-line` | unchanged | unchanged |
| `// phpstan-ignore SomeRule` | `.` appended — directive broken | unchanged |
| `// This is a normal comment` | `.` appended | `.` appended (correct) |
