<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Author generator.
 *
 * @author Joe J. Howard
 */
class Author extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
        if (is_single() && the_author() && the_author()->id !== 1)
        {
            $image = the_author_thumbnail();

            return
            [
                '@type'                => 'Person',
                '@id'                  => the_canonical_url() . '#author',
                'name'                 => the_author_name(),
                'url'                  => the_author_url(),
                'description'          => the_author_bio(),
                'image'                =>
                [
                    '@type'   => 'ImageObject',
                    '@id'     => $this->Request->environment()->HTTP_HOST . '/#authorlogo',
                    'url'     =>
                    [
                        $image->imgSize(),
                        $image->imgSize('1_1'),
                        $image->imgSize('4_3'),
                        $image->imgSize('16_9'),
                    ],
                ],
            ];
        }

        return [];
	}
}
