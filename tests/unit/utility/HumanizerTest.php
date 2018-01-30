<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\utility;

use tests\TestCase;
use kanso\framework\utility\Humanizer;

/**
 * @group unit
 */
class HumanizerTest extends TestCase
{
	/**
	 *
	 */
	public function testFileSize()
	{
		# Bytes
		$this->assertEquals('1 byte', Humanizer::fileSize(1));
		$this->assertEquals('100 bytes', Humanizer::fileSize(100));
		
		# kilobytes
		$this->assertEquals('1 KB', Humanizer::fileSize(1000));
		$this->assertEquals('1.3 KB', Humanizer::fileSize(1300));

		# Megabytes
		$this->assertEquals('1 MB', Humanizer::fileSize(1000000));
		$this->assertEquals('1.3 MB', Humanizer::fileSize(1300000));

		# Gigabytes
		$this->assertEquals('1 GB', Humanizer::fileSize(1000000000));
		$this->assertEquals('1.3 GB', Humanizer::fileSize(1300000000));
	}

	/**
	 *
	 */
	public function testTimeAgo()
	{
		$this->assertEquals('1 second', Humanizer::timeAgo(strtotime('-1 second')));
		$this->assertEquals('30 minutes', Humanizer::timeAgo(strtotime('-30 minutes')));
		$this->assertEquals('1 hour', Humanizer::timeAgo(strtotime('-1 hour')));
		$this->assertEquals('2 hours', Humanizer::timeAgo(strtotime('-2 hours')));
		$this->assertEquals('1 day', Humanizer::timeAgo(strtotime('-1 day')));
		$this->assertEquals('2 days', Humanizer::timeAgo(strtotime('-2 days')));
		$this->assertEquals('1 week', Humanizer::timeAgo(strtotime('-1 week')));
		$this->assertEquals('2 weeks', Humanizer::timeAgo(strtotime('-2 weeks')));
		$this->assertEquals('1 year', Humanizer::timeAgo(strtotime('-1 year')));
		$this->assertEquals('2 years', Humanizer::timeAgo(strtotime('-2 years')));
	}

	/**
	 *
	 */
	public function testPluralize()
	{
		// Regulars
		$this->assertEquals('apples', Humanizer::pluralize('apple'));
	 	$this->assertEquals('quizzes', Humanizer::pluralize('quiz'));
	 	$this->assertEquals('mice', Humanizer::pluralize('mouse'));
	 	$this->assertEquals('slices', Humanizer::pluralize('slice'));
	 	$this->assertEquals('beehives', Humanizer::pluralize('beehive'));
	 	$this->assertEquals('wives', Humanizer::pluralize('wife'));
	 	$this->assertEquals('thieves', Humanizer::pluralize('thief'));
	 	$this->assertEquals('sheaves', Humanizer::pluralize('sheaf'));
	 	$this->assertEquals('leaves', Humanizer::pluralize('leaf'));
	 	$this->assertEquals('loaves', Humanizer::pluralize('loaf'));
	 	$this->assertEquals('flies', Humanizer::pluralize('fly'));
	 	$this->assertEquals('oases', Humanizer::pluralize('oasis'));
	 	$this->assertEquals('tomatoes', Humanizer::pluralize('tomato'));
	 	$this->assertEquals('potatoes', Humanizer::pluralize('potato'));
	 	$this->assertEquals('echoes', Humanizer::pluralize('echo'));
	 	$this->assertEquals('heroes', Humanizer::pluralize('hero'));
	 	$this->assertEquals('vetoes', Humanizer::pluralize('veto'));
	 	$this->assertEquals('buses', Humanizer::pluralize('bus'));
	 	$this->assertEquals('octopi', Humanizer::pluralize('octopus'));
	 	$this->assertEquals('viri', Humanizer::pluralize('virus'));
	 	$this->assertEquals('axes', Humanizer::pluralize('axis'));
	 	$this->assertEquals('pluses', Humanizer::pluralize('plus'));
	 	$this->assertEquals('humans', Humanizer::pluralize('human'));
	 	$this->assertEquals('men', Humanizer::pluralize('man'));
	 	$this->assertEquals('women', Humanizer::pluralize('woman'));

	 	// Irregulars
	 	$this->assertEquals('aliases', Humanizer::pluralize('alias'));
	 	$this->assertEquals('audio', Humanizer::pluralize('audio'));
	 	$this->assertEquals('children', Humanizer::pluralize('child'));
	 	$this->assertEquals('deer', Humanizer::pluralize('deer'));
	 	$this->assertEquals('equipment', Humanizer::pluralize('equipment'));
	 	$this->assertEquals('fish', Humanizer::pluralize('fish'));
	 	$this->assertEquals('feet', Humanizer::pluralize('foot'));
	 	$this->assertEquals('geese', Humanizer::pluralize('goose'));
	 	$this->assertEquals('gold', Humanizer::pluralize('gold'));
	 	$this->assertEquals('information', Humanizer::pluralize('information'));
	 	$this->assertEquals('money', Humanizer::pluralize('money'));
	 	$this->assertEquals('oxen', Humanizer::pluralize('ox'));
	 	$this->assertEquals('police', Humanizer::pluralize('police'));
	 	$this->assertEquals('series', Humanizer::pluralize('series'));
	 	$this->assertEquals('sexes', Humanizer::pluralize('sex'));
	 	$this->assertEquals('sheep', Humanizer::pluralize('sheep'));
	 	$this->assertEquals('species', Humanizer::pluralize('species'));
	 	$this->assertEquals('teeth', Humanizer::pluralize('tooth'));

	 	// Should not pluralize when number === 1
	 	$this->assertEquals('apple', Humanizer::pluralize('apple', 1));
	}
}