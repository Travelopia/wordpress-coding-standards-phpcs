<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use Travelopia\WordPressCodingStandards\TravelopiaFixersConfig;

$finder = Finder::create()
	->in( __DIR__ )
	->name( '*.php' )
	->ignoreVCS( true )
	->exclude( 'vendor' );

$config = TravelopiaFixersConfig::create()
	->setRiskyAllowed( true )
	->setIndent( "\t" )
	->setLineEnding( "\n" )
	->setParallelConfig( ParallelConfigFactory::detect() )
	->setRules( TravelopiaFixersConfig::getRules() )
	->setFinder( $finder );

return $config;
