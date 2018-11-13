<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator;

/**
 * Input validation.
 *
 * @author Joe J. Howard
 */
class Filter
{
	/**
	 * Rules.
	 *
	 * @var array
	 */
	private $filters =
	[
		'email'       => Email::class,
		'float'       => Float::class,
		'html_decode' => HtmlDecode::class,
		'html_encode' => HtmlEncode::class,
		'integer'     => Integer::class,
		'json'        => Json::class,
		'lowercase'   => LowerCase::class,
		'numeric'     => Numeric::class,
		'string'      => String::class,
		'strip_tags'  => StripTags::class,
		'trim'        => Trim::class,
		'uppercase'   => UpperCase::class,
		'url_decode'  => UrlDecode::class,
		'url_encode'  => UrlEncode::class,
	];

}
