<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session\storage;

/**
 * Session encrypt write / read decrypt.
 *
 * @author Joe J. Howard
 */
interface StoreInterface
{
	/**
	 * Get and/or set the current session save path.
	 *
	 * @param string $path Directory to save session data to
	 */
	public function session_save_path(string $path = '');

	/**
	 * Start the session.
	 */
	public function session_start();

	/**
	 * Destroy the session.
	 */
	public function session_destroy();

	/**
	 * Set and/or gets the current session id.
	 *
	 * @param  string|null $id Id to set (optional) (default null)
	 * @return string
	 */
	public function session_id(string $id = null);

	/**
	 * Get and/or set the session cookie name.
	 *
	 * @param string|null $name The cookie name to set
	 */
	public function session_name(string $name = null);

	/**
	 * Regenerate the session id.
	 *
	 * @param bool $deleteOldSession Delete the old session (optional) (default false)
	 */
	public function session_regenerate_id(bool $deleteOldSession = false);

	/**
	 * Set the cookie parameters.
	 *
	 * @param array $params Array of cookie parameters
	 * @see    http://php.net/manual/en/function.session-set-cookie-params.php
	 */
	public function session_set_cookie_params(array $params);

	/**
	 * Get the cookie parameters.
	 *
	 * @return array
	 */
	public function session_get_cookie_params();

	/**
	 * Collect garbage (delete expired sessions).
	 */
	public function session_gc();

	/**
	 * Read and return the session data.
	 *
	 * @return array|null
	 */
	public function read();

	/**
	 * Write the session data.
	 *
	 * @param array $data Data to write to session
	 */
	public function write(array $data);

	/**
	 * Send the session cookie.
	 */
	public function send();
}
