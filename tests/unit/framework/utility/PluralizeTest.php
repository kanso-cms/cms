<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\utility;

use kanso\framework\utility\Pluralize;
use tests\TestCase;

/**
 * @group unit
 */
class PluralizeTest extends TestCase
{
	/**
	 *
	 */
	public function testPluralize()
	{
		// Regulars
		$this->assertEquals('apples', Pluralize::convert('apple'));
	 	$this->assertEquals('quizzes', Pluralize::convert('quiz'));
	 	$this->assertEquals('mice', Pluralize::convert('mouse'));
	 	$this->assertEquals('slices', Pluralize::convert('slice'));
	 	$this->assertEquals('beehives', Pluralize::convert('beehive'));
	 	$this->assertEquals('wives', Pluralize::convert('wife'));
	 	$this->assertEquals('thieves', Pluralize::convert('thief'));
	 	$this->assertEquals('sheaves', Pluralize::convert('sheaf'));
	 	$this->assertEquals('leaves', Pluralize::convert('leaf'));
	 	$this->assertEquals('loaves', Pluralize::convert('loaf'));
	 	$this->assertEquals('flies', Pluralize::convert('fly'));
	 	$this->assertEquals('oases', Pluralize::convert('oasis'));
	 	$this->assertEquals('tomatoes', Pluralize::convert('tomato'));
	 	$this->assertEquals('potatoes', Pluralize::convert('potato'));
	 	$this->assertEquals('echoes', Pluralize::convert('echo'));
	 	$this->assertEquals('heroes', Pluralize::convert('hero'));
	 	$this->assertEquals('vetoes', Pluralize::convert('veto'));
	 	$this->assertEquals('buses', Pluralize::convert('bus'));
	 	$this->assertEquals('octopi', Pluralize::convert('octopus'));
	 	$this->assertEquals('viri', Pluralize::convert('virus'));
	 	$this->assertEquals('axes', Pluralize::convert('axis'));
	 	$this->assertEquals('pluses', Pluralize::convert('plus'));
	 	$this->assertEquals('humans', Pluralize::convert('human'));
	 	$this->assertEquals('men', Pluralize::convert('man'));
	 	$this->assertEquals('women', Pluralize::convert('woman'));

	 	// Irregulars
	 	$this->assertEquals('aliases', Pluralize::convert('alias'));
	 	$this->assertEquals('audio', Pluralize::convert('audio'));
	 	$this->assertEquals('children', Pluralize::convert('child'));
	 	$this->assertEquals('deer', Pluralize::convert('deer'));
	 	$this->assertEquals('equipment', Pluralize::convert('equipment'));
	 	$this->assertEquals('fish', Pluralize::convert('fish'));
	 	$this->assertEquals('feet', Pluralize::convert('foot'));
	 	$this->assertEquals('geese', Pluralize::convert('goose'));
	 	$this->assertEquals('gold', Pluralize::convert('gold'));
	 	$this->assertEquals('information', Pluralize::convert('information'));
	 	$this->assertEquals('money', Pluralize::convert('money'));
	 	$this->assertEquals('oxen', Pluralize::convert('ox'));
	 	$this->assertEquals('police', Pluralize::convert('police'));
	 	$this->assertEquals('series', Pluralize::convert('series'));
	 	$this->assertEquals('sexes', Pluralize::convert('sex'));
	 	$this->assertEquals('sheep', Pluralize::convert('sheep'));
	 	$this->assertEquals('species', Pluralize::convert('species'));
	 	$this->assertEquals('teeth', Pluralize::convert('tooth'));

	 	// Should not pluralize when number === 1
	 	$this->assertEquals('apple', Pluralize::convert('apple', 1));
	}

}
