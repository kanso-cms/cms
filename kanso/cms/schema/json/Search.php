<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Search generator.
 *
 * @author Joe J. Howard
 */
class Search extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
		return
        [
            '@type'       => 'SearchAction',
            '@id'         => $this->Request->environment()->HTTP_HOST . '/#search',
            'target'      => $this->Request->environment()->HTTP_HOST . '/search-results/?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ];
	}
}
