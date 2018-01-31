<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\utility;

use tests\TestCase;
use kanso\framework\utility\Gump;

/**
 * @group unit
 */
class GumpTest extends TestCase
{
	/**
	 *
	 */
	public function testValidation()
	{
		$validator = new GUMP;

		$rules = array(
			'missing'   	=> 'required',
			'email'     	=> 'valid_email',
			'max_len'   	=> 'max_len,1',
			'min_len'   	=> 'min_len,4',
			'exact_len' 	=> 'exact_len,10',
			'alpha'	       	=> 'alpha',
			'alpha_numeric' => 'alpha_numeric',
			'alpha_dash'	=> 'alpha_dash',
			'numeric'		=> 'numeric',
			'integer'		=> 'integer',
			'boolean'		=> 'boolean',
			'float'			=> 'float',
			'valid_url'		=> 'valid_url',
			'url_exists'	=> 'url_exists',
			'valid_ip'		=> 'valid_ip',
			'valid_ipv4'	=> 'valid_ipv4',
			'valid_ipv6'	=> 'valid_ipv6',
			'valid_name'    => 'valid_name',
			'contains'		=> 'contains,free pro basic'
		);

		$invalid_data = array(
			'missing'   	=> '',
			'email'     	=> "not a valid email\r\n",
			'max_len'   	=> "1234567890",
			'min_len'   	=> "1",
			'exact_len' 	=> "123456",
			'alpha'	       	=> "*(^*^*&",
			'alpha_numeric' => "abcdefg12345+\r\n\r\n\r\n",
			'alpha_dash'	=> "ab<script>alert(1);</script>cdefg12345-_+",
			'numeric'		=> "one, two\r\n",
			'integer'		=> "1,003\r\n\r\n\r\n\r\n",
			'boolean'		=> "this is not a boolean\r\n\r\n\r\n\r\n",
			'float'			=> "not a float\r\n",
			'valid_url'		=> "\r\n\r\nhttp://add",
			'url_exists'	=> "http://asdasdasd354.gov",
			'valid_ip'		=> "google.com",
			'valid_ipv4'    => "google.com",
			'valid_ipv6'    => "google.com",
			'valid_name' 	=> '*&((*S))(*09890uiadaiusyd)',
			'contains'		=> 'premium'
		);

		$valid_data = array(
			'missing'   	=> 'This is not missing',
			'email'     	=> 'sean@wixel.net',
			'max_len'   	=> '1',
			'min_len'   	=> '1234',
			'exact_len' 	=> '1234567890',
			'alpha'	       	=> 'ÈÉÊËÌÍÎÏÒÓÔasdasdasd',
			'alpha_numeric' => 'abcdefg12345',
			'alpha_dash'	=> 'abcdefg12345-_',
			'numeric'		=> 2.00,
			'integer'		=> 3,
			'boolean'		=> FALSE,
			'float'			=> 10.10,
			'valid_url'		=> 'http://wixel.net',
			'url_exists'	=> 'http://wixel.net',
			'valid_ip'		=> '69.163.138.23',
			'valid_ipv4'    => "255.255.255.255",
			'valid_ipv6'    => "2001:0db8:85a3:08d3:1319:8a2e:0370:7334",
			'valid_name' 	=> 'Sean Nieuwoudt',
			'contains'		=> 'free'
		);

		$validator->sanitize($invalid_data);
		$validator->validate($invalid_data, $rules);
		
		$this->assertEquals(18, count($validator->get_readable_errors()));
		$this->assertEquals(19, count($valid_data));
	}
}