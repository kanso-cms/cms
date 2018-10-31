<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response\exceptions;

use ErrorException;

/**
 * Stop the application gracefully.
 *
 * @author Joe J. Howard
 */
class Stop extends ErrorException
{
}
