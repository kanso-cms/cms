<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Logo generator.
 *
 * @author Joe J. Howard
 */
class Logo extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
		return
        [
            '@type'   => 'ImageObject',
            '@id'     => $this->Request->environment()->HTTP_HOST . '/#logo',
            'url'     => $this->Config->get('cms.schema.logo'),
            'width'   => 512,
            'height'  => 512,
            'caption' => $this->Config->get('cms.schema.brand'),
        ];
	}
}
