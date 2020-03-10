<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\shortcode\Shortcodes;
use kanso\framework\application\services\Service;

/**
 * Crm Service.
 *
 * @author Joe J. Howard
 */
class ShortcodeService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Shortcodes', function()
		{
			return new Shortcodes;
		});
	}
}
