<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\framework\utility\Str;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Humanizer;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;

/**
 * Admin email logs
 *
 * @author Joe J. Howard
 */
class EmailLogs extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn)
        {
            return $this->parseGet();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        if ($this->isLoggedIn)
        {
            return $this->parsePost();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        # Process any AJAX requests here
        # 
        # Returning an associative array will
        # send a JSON response to the client
        
        # Returning false sends a 404 
        return false;
    }

    /**
     * Parse the $_GET request variables and filter the leads for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        # Prep the response
        $response =
        [
            'emails'        => $this->loadEmails(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
            'active_tab'    => 'email-log',
        ];

        # If the leads are empty,
        # There's no need to check for max pages
        if (!empty($response['emails']))
        {
            $response['max_page'] = $this->loadEmails(true);
        }

        return $response;
    }

     /**
     * Parse the $_GET request variables and filter the leads for the requested page.
     *
     * @access private
     * @return array
     */
    private function parsePost(): array
    {
        $validate = $this->validatePost();
        
        if (!$validate)
        {
            return false;
        }

        $id           = $this->post['id'];
        $path         = $this->Config->get('email.log_dir');
        $file         = $path.'/'.$id;
        $contentFile  = $path.'/'.$id.'_content';

        if ($this->Filesystem->exists($file) && $this->Filesystem->exists($contentFile))
        {
            $data    = unserialize($this->Filesystem->getContents($file));
            $content = $this->Filesystem->getContents($contentFile);

            $this->Email->send($data['to_email'], $data['from_name'], $data['from_email'], $data['subject'], $content, $data['format']);

            return $this->postMessage('success', 'Email was successfully re-sent');
        }
        
        return $this->postMessage('danger', 'The requested email could not be found.');
    }

    /**
     * Validates all POST variables are set
     * 
     * @access private
     * @return bool
     */
    private function validatePost(): bool
    {
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new InvalidTokenException('Bad Admin Panel POST Request. The CSRF token was either not provided or was invalid.');
        }
        
        # Validation
        if (!isset($this->post['id']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        return true;
    }

    /**
     * Check if the GET URL queries are either empty or set to defaults
     *
     * @access private
     * @return bool
     */
    private function emptyQueries(): bool
    {
        $queries = $this->getQueries();

        return (
            $queries['search'] === false && 
            $queries['page']   === 0 && 
            $queries['sort']   === 'date' && 
            $queries['filter'] === 'all'
        );
    }

    /**
     * Returns the requested GET queries with defaults
     *
     * @access private
     * @return array
     */
    private function getQueries(): array
    {
        # Get queries
        $queries = $this->Request->queries();

        # Set defaults
        if (!isset($queries['search']))   $queries['search']   = false;
        if (!isset($queries['page']))     $queries['page']     = 0;
        if (!isset($queries['sort']))     $queries['sort']     = 'date';
        if (!isset($queries['filter']))   $queries['filter']   = 'all';

        return $queries;
    }

    /**
     * Returns the list of leads for display
     *
     * @access private
     * @param  bool $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadEmails(bool $checkMaxPages = false)
    {
        $queries      = $this->getQueries();
        $page         = ((int)$queries['page']);
        $sort         = $queries['sort'];
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $filter       = $queries['filter'];
        $search       = $queries['search'];
        $path         = $this->Config->get('email.log_dir');
        $files        = $this->Filesystem->list($path, ['..', '.', '.ds_store', '.DS_Store', '.gitignore' ]);

        # Remove contents files
        foreach ($files as $i => $file)
        {
            if (Str::contains($file, '_content'))
            {
                unset($files[$i]);
            }
        }

        $files = array_values($files);

        # Sort the emails
        usort($files, function($a, $b) use ($path, $sort)
        {
            $a = unserialize($this->Filesystem->getContents($path.'/'.$a));
            $b = unserialize($this->Filesystem->getContents($path.'/'.$b));

            if ($sort === 'date')
            {
                return $a['date'] < $b['date'];
            }

            return strcmp($a[$sort], $b[$sort]); 
        });

        # Filter the email type
        if ($filter !== 'all')
        {
            foreach ($files as $i => $file)
            {
                $contents = unserialize($this->Filesystem->getContents($path.'/'.$file));

                if ($filter === 'html')
                {
                    if ($contents['format'] !== 'html')
                    {
                        unset($files[$i]);
                    }
                }
                else if ($contents['format'] === 'html')
                {
                    unset($files[$i]);
                }
            }

            $files = array_values($files);
        }

        # Search
        if ($search)
        {
            foreach ($files as $i => $file)
            {
                $contents = unserialize($this->Filesystem->getContents($path.'/'.$file));

                if ( Str::contains($contents['to_email'], $search) || 
                     Str::contains($contents['from_email'], $search) || 
                     Str::contains($contents['from_name'], $search) || 
                     Str::contains($contents['subject'], $search))
                {
                    continue;
                }
                else
                {
                    unset($files[$i]);
                }
            }

            $files = array_values($files);
        }
        
        # Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($files) / $perPage);
        }

        $results = [];

        foreach ($files as $i => $file)
        {
            $contents       = unserialize($this->Filesystem->getContents($path.'/'.$file));
            $contents['id'] = $file;
            $results[]      = $contents;
        }

        # Return the paginated results
        $paged = Arr::paginate($results, $page, $perPage);
        $page  = $page === 1 || $page === 0 ? 0 : $page-1;

        if (!$paged || !isset($paged[$page]))
        {
            return [];
        }

        return $paged[$page];
    }
}
