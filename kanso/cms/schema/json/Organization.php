<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Organization generator.
 *
 * @author Joe J. Howard
 */
class Organization extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
		return
		[
            '@type'       => 'Organization',
            '@id'         => $this->Request->environment()->HTTP_HOST . '/#organization',
            'name'        => $this->Config->get('cms.schema.brand'),
            'email'       => $this->Config->get('cms.schema.email'),
            'url'         => $this->Request->environment()->HTTP_HOST,
            'description' => $this->Config->get('cms.site_description'),
            'brand'       =>
            [
                '@id' => $this->Request->environment()->HTTP_HOST . '/#brand',
            ],
            'logo'        =>
            [
                '@id' => $this->Request->environment()->HTTP_HOST . '/#logo',
            ],
            'address'     =>
            [
                '@id' => $this->Request->environment()->HTTP_HOST . '/#address',
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
