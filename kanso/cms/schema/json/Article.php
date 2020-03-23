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
class Article extends JsonGenerator implements JsonInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        if (is_single())
        {
            return
            [
                '@type'            => 'Article',
                '@id'              => the_canonical_url() . '#article',
                'headline'         => the_title(),
                'articleBody'      => the_content(the_post_id(), true),
                'datePublished'    => the_time('c'),
                'dateModified'     => the_modified_time('c'),
                'articleSection'   => the_categories_list(),
                'wordCount'        => str_word_count(the_content(the_post_id(), true)),
                'inLanguage'       => 'en',
                'publisher' =>
                [
                    '@id' => $this->Request->environment()->HTTP_HOST . '/#organization',
                ],
                'isPartOf' =>
                [
                    '@id' => the_canonical_url() . '#webpage',
                ],
                'mainEntityOfPage' =>
                [
                    '@id' => the_canonical_url() . '#webpage',
                ],
                'image' =>
                [
                    '@id' => the_canonical_url() . '#primaryimage',
                ],
                'author' =>
                [
                    '@id' => the_author()->id !== 1 ? the_canonical_url() . '#author' : $this->Request->environment()->HTTP_HOST . '/#organization',
                ],
            ];
        }

        return [];
    }
}
