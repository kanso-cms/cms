<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

/**
 * CMS Query search methods.
 *
 * @author Joe J. Howard
 */
class Search extends Helper
{
    /**
     * Returns the searched query for search result requests.
     *
     * @return string|null
     */
    public function search_query()
    {
        if ($this->parent->is_search())
        {
            return urldecode($this->parent->searchQuery);
        }

        return null;
    }

    /**
     * Return the HTML for the search form.
     *
     * @return string
     */
    public function get_search_form(): string
    {
        // Load from template if it exists
        $formTemplate = $this->parent->theme_directory() . DIRECTORY_SEPARATOR . 'searchform.php';

        if (file_exists($formTemplate))
        {
            return $this->parent->include_template('searchform');
        }

        return '

            <form role="search" method="get" action="' . $this->parent->home_url() . '/search-results/">

                <fieldset>
                        
                        <label for="search_input">Search: </label>
                        
                        <input type="search" name="q" id="search_input" placeholder="Search...">

                        <button type"submit">Search</button>

                </fieldset>
                
            </form>

        ';
    }
}
