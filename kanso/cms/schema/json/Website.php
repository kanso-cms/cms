<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Website generator.
 *
 * @author Joe J. Howard
 */
class Website extends JsonGenerator implements JsonInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        return
        [
            '@type'           => 'WebSite',
            '@id'             => $this->Request->environment()->HTTP_HOST . '/#website',
            'url'             => $this->Request->environment()->HTTP_HOST,
            'name'            => $this->Config->get('cms.schema.brand'),
            'headline'        => $this->Config->get('cms.site_title'),
            'description'     => $this->Config->get('cms.site_description'),
            'potentialAction' =>
            [
                '@id' => $this->Request->environment()->HTTP_HOST . '/#search',
            ],
            'publisher'       =>
            [
                '@id' => $this->Request->environment()->HTTP_HOST . '/#organization',
            ],
        ];
    }
}
