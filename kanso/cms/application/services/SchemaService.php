<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\schema\Schema;
use kanso\framework\application\services\Service;

/**
 * Schema.org service.
 *
 * @author Joe J. Howard
 */
class SchemaService extends Service
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->container->setInstance('Schema', new Schema);
    }
}
