<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\validator;

use kanso\framework\ioc\Container;
use kanso\framework\validator\Validator;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class ValidatorTest extends TestCase
{
	/**
	 *
	 */
	public function testValids()
	{
		$fields =
		[
			'alpha_dash'               => 'foo-bar',
			'alpha'                    => 'foobar',
			'alphanumeric'             => 'foobar32',
			'email'                    => 'foo@bar.com',
			'exact_length'             => 'fooba',
			'float'                    => '33.3',
			'greater_than_or_equal_to' => '10',
			'greater_than'             => '31',
			'in'                       => 'foo',
			'integer'                  => '11',
			'ip'                       => '192.168.11.1',
			'json'                     => '[{"foo":"baz"}]',
			'less_than_or_equal_to'    => '5',
			'less_than'                => '9',
			'match'                    => 'foo',
			'max_length'               => '123456789',
			'min_length'               => '1234567891011',
			'not_in'                   => 'foobax',
			'regex'                    => 'foobar',
			'required'                 => 'required',
			'url'                      => 'http://foo.com',
		];

		$rules =
		[
			'alpha_dash'               => ['required', 'alpha_dash'],
			'alpha'                    => ['required', 'alpha'],
			'alphanumeric'             => ['required', 'alpha_numeric'],
			'email'                    => ['required', 'email'],
			'exact_length'             => ['required', 'exact_length(5)'],
			'float'                    => ['required', 'float'],
			'greater_than_or_equal_to' => ['required', 'greater_than_or_equal_to(9)'],
			'greater_than'             => ['required', 'greater_than(30)'],
			'in'                       => ['required', 'in(["foo","bar","baz"])'],
			'integer'                  => ['required', 'integer'],
			'ip'                       => ['required', 'ip'],
			'json'                     => ['required', 'json'],
			'less_than_or_equal_to'    => ['required', 'less_than_or_equal_to(10)'],
			'less_than'                => ['required', 'less_than(10)'],
			'match'                    => ['required', 'match(foo)'],
			'max_length'               => ['required', 'max_length(10)'],
			'min_length'               => ['required', 'min_length(10)'],
			'not_in'                   => ['required', 'not_in(["foo","bar","baz"])'],
			'regex'                    => ['required', 'regex(/^[a-z]+$/i)'],
			'required'                 => ['required'],
			'url'                      => ['required', 'url'],
		];

		$container = Container::instance();

		$validator = new Validator($fields, $rules, [], $container);

		$this->assertTrue($validator->isValid());

		$this->assertFalse($validator->isInValid());

		$this->assertTrue(empty($validator->getErrors()));
	}

	/**
	 *
	 */
	public function testInValids()
	{
		$fields =
		[
			'alpha_dash'               => 'foo-bar foo',
			'alpha'                    => 'foobar-baz',
			'alphanumeric'             => 'foobar-32!!',
			'email'                    => 'www.bar.com',
			'exact_length'             => 'foobaz',
			'float'                    => '33',
			'greater_than_or_equal_to' => '3',
			'greater_than'             => '4',
			'in'                       => 'fooz',
			'integer'                  => '11.44',
			'ip'                       => '192.168',
			'json'                     => '[{"foo":baz"}]',
			'less_than_or_equal_to'    => '12',
			'less_than'                => '33',
			'match'                    => 'foobaz',
			'max_length'               => 'foobazfoobazfoobazfoobaz',
			'min_length'               => 'foobaz',
			'not_in'                   => 'foo',
			'regex'                    => 'foobds 32ar $$# {}',
			'url'                      => 'foo@me.com',
		];

		$rules =
		[
			'alpha_dash'               => ['required', 'alpha_dash'],
			'alpha'                    => ['required', 'alpha'],
			'alphanumeric'             => ['required', 'alpha_numeric'],
			'email'                    => ['required', 'email'],
			'exact_length'             => ['required', 'exact_length(5)'],
			'float'                    => ['required', 'float'],
			'greater_than_or_equal_to' => ['required', 'greater_than_or_equal_to(9)'],
			'greater_than'             => ['required', 'greater_than(30)'],
			'in'                       => ['required', 'in(["foo","bar","baz"])'],
			'integer'                  => ['required', 'integer'],
			'ip'                       => ['required', 'ip'],
			'json'                     => ['required', 'json'],
			'less_than_or_equal_to'    => ['required', 'less_than_or_equal_to(10)'],
			'less_than'                => ['required', 'less_than(10)'],
			'match'                    => ['required', 'match(foo)'],
			'max_length'               => ['required', 'max_length(10)'],
			'min_length'               => ['required', 'min_length(10)'],
			'not_in'                   => ['required', 'not_in(["foo","bar","baz"])'],
			'regex'                    => ['required', 'regex(/^[a-z]+$/i)'],
			'required'                 => ['required'],
			'url'                      => ['required', 'url'],
		];

		$container = Container::instance();

		$validator = new Validator($fields, $rules, [], $container);

		$this->assertFalse($validator->isValid());

		$this->assertTrue($validator->isInValid());

		$this->assertTrue(count($validator->getErrors()) === count($rules));
	}

	/**
	 *
	 */
	public function testFiltersValid()
	{
		$fields =
		[
			'email'       => 'foo@bar.com',
			'float'       => '44.3',
			'html_decode' => "I'll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now",
			'html_encode' => "I'll \"walk\" the <b>dog</b> now",
			'integer'     => '12',
			'json'        => '[{"foo" : "bar"}]',
			'lowercase'   => 'foobar',
			'numeric'     => '12345',
			'string'      => 'foo!@<bar',
			'strip_tags'  => '<h1>foo</h1>',
			'trim'        => ' foo ',
			'uppercase'   => 'foobar',
			'url_decode'  => 'foo bar',
			'url_encode'  => 'foo bar',
		];

		$filters =
		[
			'email'       => ['email'],
			'float'       => ['float'],
			'html_decode' => ['html_decode'],
			'html_encode' => ['html_encode'],
			'integer'     => ['integer'],
			'json'        => ['json'],
			'lowercase'   => ['lowercase'],
			'numeric'     => ['numeric'],
			'string'      => ['string'],
			'strip_tags'  => ['strip_tags'],
			'trim'        => ['trim'],
			'uppercase'   => ['uppercase'],
			'url_decode'  => ['url_decode'],
			'url_encode'  => ['url_encode'],
		];

		$expected =
		[
			'email'       => 'foo@bar.com',
			'float'       => 44.3,
			'html_decode' => 'I\'ll "walk" the <b>dog</b> now',
			'html_encode' => 'I&#039;ll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now',
			'integer'     => 12,
			'json'        => [['foo' => 'bar']],
			'lowercase'   => 'foobar',
			'numeric'     => '12345',
			'string'      => 'foo!@',
			'strip_tags'  => 'foo',
			'trim'        => 'foo',
			'uppercase'   => 'FOOBAR',
			'url_decode'  => 'foo bar',
			'url_encode'  => 'foo+bar',
		];

		$container = Container::instance();

		$validator = new Validator($fields, [], $filters, $container);

		$this->assertEquals($expected, $validator->filter());
	}

	/**
	 *
	 */
	public function testFiltersInValid()
	{
		$fields =
		[
			'email'       => 'foo',
			'float'       => '11',
			'integer'     => '33.1',
			'json'        => '[ "bar"}]',
			'numeric'     => 'foo',
		];

		$filters =
		[
			'email'       => ['email'],
			'float'       => ['float'],
			'integer'     => ['integer'],
			'json'        => ['json'],
			'numeric'     => ['numeric'],
		];

		$expected =
		[
			'email'       => 'foo',
			'float'       => 11.0,
			'integer'     => 33,
			'json'        => null,
			'numeric'     => '',
		];

		$container = Container::instance();

		$validator = new Validator($fields, [], $filters, $container);

		$this->assertEquals($expected, $validator->filter());
	}

}
