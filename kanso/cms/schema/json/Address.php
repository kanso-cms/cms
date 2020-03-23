<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Address generator.
 *
 * @author Joe J. Howard
 */
class Address extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
		return
        [
            '@type'           => 'PostalAddress',
            '@id'             => $this->Request->environment()->HTTP_HOST . '/#address',
            'streetAddress'   => $this->Config->get('cms.schema.streetAddress'),
            'addressLocality' => $this->Config->get('cms.schema.addressLocality'),
            'addressRegion'   => $this->Config->get('cms.schema.addressRegion'),
            'postalCode'      => $this->Config->get('cms.schema.postalCode'),
            'addressCountry'  => $this->Config->get('cms.schema.addressCountry'),
        ];
	}
}
