<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application routes
 *
 * @author Joe J. Howard
 */

# Defined local variables
$router     = $this->container->Router;
$config     = $this->container->Config;
$SQL        = $this->container->Database->connection()->builder();
$blogPrefix = !empty($config->get('cms.blog_location')) ? '/'.$config->get('cms.blog_location') : '';

require_once 'routes/Admin.php';

require_once 'routes/Home.php';

require_once 'routes/Taxonomy.php';

require_once 'routes/Attachment.php';

require_once 'routes/Pages.php';

require_once 'routes/Search.php';

require_once 'routes/Comments.php';

require_once 'routes/Sitemap.php';

require_once 'routes/Posts.php';
