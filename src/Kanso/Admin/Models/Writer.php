<?php

namespace Kanso\Admin\Models;

/**
 * GET Model for writer page
 *
 * This model hadles the GET requests to the writer
 * application
 *
 * The class is instantiated by the respective controller
 */
class Writer
{

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        
        
    }

    /**
     * Parse the $_GET request variables
     *
     * This loads an existing article if one exists
     * 
     * @return array
     */
	public function parseGet()
	{
        $template  = \Kanso\Kanso::getInstance()->Bookkeeper->create();
        $queries   = \Kanso\Kanso::getInstance()->Request->queries();
        $tags_list = '';
        if (isset($queries['id'])) {
            $_template  = \Kanso\Kanso::getInstance()->Bookkeeper->existing(intval($queries['id']));
            if ($_template) {
                $template  = $_template;
                $tags_list = \Kanso\Kanso::getInstance()->Query->the_tags_list(intval($queries['id']));
                $this->overrideTitle($_template->title);
            }
        }

        return ['tags_list' => $tags_list, 'the_post' => $template];
	}

    private function overrideTitle($title)
    {

        $filter = function() use ($title) {
            return "Editing - $title";
        };
        
        \Kanso\Kanso::getInstance()->Filters->on('adminPageTitle', $filter);

    }

}