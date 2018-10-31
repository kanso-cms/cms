<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\crawler\fixtures;

use kanso\framework\common\ArrayAccessTrait;

/**
 * Crawler exclusions.
 *
 * @author Joe J. Howard
 */
class Exclusions
{
	use ArrayAccessTrait;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct()
	{
		$this->overwrite([
			'Safari.[\d\.]*',
	        'Firefox.[\d\.]*',
	        ' Chrome.[\d\.]*',
	        'Chromium.[\d\.]*',
	        'MSIE.[\d\.]',
	        'Opera\/[\d\.]*',
	        'Mozilla.[\d\.]*',
	        'AppleWebKit.[\d\.]*',
	        'Trident.[\d\.]*',
	        'Windows NT.[\d\.]*',
	        'Android [\d\.]*',
	        'Macintosh.',
	        'Ubuntu',
	        'Linux',
	        '[ ]Intel',
	        'Mac OS X [\d_]*',
	        '(like )?Gecko(.[\d\.]*)?',
	        'KHTML,',
	        'CriOS.[\d\.]*',
	        'CPU iPhone OS ([0-9_])* like Mac OS X',
	        'CPU OS ([0-9_])* like Mac OS X',
	        'iPod',
	        'compatible',
	        'x86_..',
	        'i686',
	        'x64',
	        'X11',
	        'rv:[\d\.]*',
	        'Version.[\d\.]*',
	        'WOW64',
	        'Win64',
	        'Dalvik.[\d\.]*',
	        ' \.NET CLR [\d\.]*',
	        'Presto.[\d\.]*',
	        'Media Center PC',
	        'BlackBerry',
	        'Build',
	        'Opera Mini\/\d{1,2}\.\d{1,2}\.[\d\.]*\/\d{1,2}\.',
	        'Opera',
	        ' \.NET[\d\.]*',
	        'cubot',
	        '; M bot',
	        '; B bot',
	        '; IDbot',
	        '; ID bot',
	        '; POWER BOT',
	        ';', // Remove the following characters ;
		]);
	}
}
