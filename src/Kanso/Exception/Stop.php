<?php
namespace Kanso\Exception;

/**
 * Stop Exception
 *
 * This Exception is thrown when the Kanso application needs to abort
 * processing and return control flow to the outer PHP script.
 */
class Stop extends \Exception
{
}