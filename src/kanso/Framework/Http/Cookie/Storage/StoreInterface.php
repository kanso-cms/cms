<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Http\Cookie\Storage;

/**
 * Cookie encrypt write / read decrypt 
 *
 * @author Joe J. Howard
 */
interface StoreInterface
{
	/**
	 * Decrypts and reads a cookie by name.
	 *
	 * @access public
	 * @param  string $key key to read from
	 * @return mixed
	 */
	public function read(string $key);

	/**
	 * Encypts and writes a cookie by name.
	 *
	 * @access public
	 * @param  string $key   key to read from
	 * @param  mixed  $value Value to save
	 */
	public function write(string $key, $value);
}
