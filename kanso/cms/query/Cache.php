<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query;

use kanso\framework\common\ArrayAccessTrait;

/**
 * Cache for query
 *
 * @author Joe J. Howard
 */
class Cache
{
	use ArrayAccessTrait;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * Converts a function, args and args number to a key
     *
     * @access public
     * @param  string $func    Method name as string
     * @param  array  $argList List of arguments
     * @param  int    $numargs Number of provided args
     * @return string
     */
    public function key(string $func, array $argList = [], int $numargs = 0): string
    {        
        $key = $func;

        for ($i = 0; $i < $numargs; $i++)
        {
            $key .= $i.':'.serialize($argList[$i]).';';
        }

        return md5($key);
    }
}
