<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

/**
 * CMS Query search methods.
 *
 * @author Joe J. Howard
 */
trait Search
{
    /**
     * Returns the searched query for search result requests.
     *
     * @access public
     * @return string|null
     */
    public function search_query()
    {
        if ($this->is_search())
        {
            return urldecode($this->searchQuery);
        }

        return null;
    }

    /**
     * Return the HTML for the search form.
     *
     * @access public
     * @return string
     */
    public function get_search_form(): string
    {
        // Load from template if it exists
        $formTemplate = $this->theme_directory() . DIRECTORY_SEPARATOR . 'searchform.php';

        if (file_exists($formTemplate))
        {
            return $this->include_template('searchform');
        }

        return '

            <form role="search" method="get" action="' . $this->home_url() . '/search-results/">

                <fieldset>
                        
                        <label for="search_input">Search: </label>
                        
                        <input type="search" name="q" id="search_input" placeholder="Search...">

                        <button type"submit">Search</button>

                </fieldset>
                
            </form>

        ';
    }
}
