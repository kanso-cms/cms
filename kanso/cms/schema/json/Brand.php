<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Brand generator.
 *
 * @author Joe J. Howard
 */
class Brand extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
		return
        [
            '@type'       => 'Brand',
            '@id'         => $this->Request->environment()->HTTP_HOST . '/#brand',
            'name'        => $this->Config->get('cms.schema.brand'),
            'url'         => $this->Request->environment()->HTTP_HOST,
            'slogan'      => $this->Config->get('cms.schema.slogan'),
            'description' => $this->Config->get('cms.site_description'),
            'logo'        =>
            [
                '@id' => $this->Request->environment()->HTTP_HOST . '/#logo',
            ],
            'sameAs'      =>
            [
                $this->Config->get('theme.social.facebook'),
                $this->Config->get('theme.social.instagram'),
                $this->Config->get('theme.social.twitter'),
            ],
        ];
	}
}
