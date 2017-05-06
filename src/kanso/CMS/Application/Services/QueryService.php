<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\CMS\Query\Query;
use Kanso\CMS\Query\QueryParser;
use Kanso\CMS\Query\Cache;

/**
 * CMS Query
 *
 * @author Joe J. Howard
 */
class QueryService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Query', function ($container) 
		{
			return new Query(
				$container->Gatekeeper,
				$container->CategoryManager,
				$container->TagManager,
				$container->UserManager,
				$container->PostManager,
				$container->MediaManager,
				$container->CommentManager,
				$container->Request,
				$container->Response,
				$container->Database->connection()->builder(),
				$container->Config, $this->loadQueryParser(),
				new Cache
			);
		});
	}

	/**
     * Loads the query parser
     *
     * @access private
     * @return \Kanso\CMS\Query\QueryParser
     */
	private function loadQueryParser(): QueryParser
	{
		return new QueryParser($this->container->Database->connection()->builder(), $this->container->PostProvider);
	}
}
