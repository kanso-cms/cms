<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\framework\file\Filesystem;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;

/**
 * Admin error logs page/list model.
 *
 * @author Joe J. Howard
 */
class ErrorLogs extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn())
        {
            return $this->parseGet();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        if ($this->isLoggedIn())
        {
            return $this->parsePost();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        // Process any AJAX requests here
        //
        // Returning an associative array will
        // send a JSON response to the client

        // Returning false sends a 404
        return false;
    }

    /**
     * Parse and validate the POST request from any submitted forms.
     *
     * @return array|false
     */
    private function parsePost()
    {
        // Validation
        if (!$this->validatePost())
        {
            return false;
        }

        // Dispatch
        if ($this->post['form_name'] === 'clear-logs')
        {
            $this->clearErrorLogs();

            return $this->postMessage('success', 'Error logs were successfully cleared!');
        }

        return false;
    }

    /**
     * Clear all error logs.
     */
    private function clearErrorLogs(): void
    {
        $logDir = $this->Config->get('application.error_handler.log_path');

        // Lets get the error files
        $logs = Filesystem::list($logDir, ['..', '.', '.DS_Store', '.gitignore']);

        // Loop and delete
        foreach ($logs as $log)
        {
            Filesystem::delete($logDir . DIRECTORY_SEPARATOR . $log);
        }
    }

    /**
     * Validates all POST variables are set.
     *
     * @return bool
     */
    private function validatePost(): bool
    {
        // Validation

        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new InvalidTokenException('Bad Admin Panel POST Request. The CSRF token was either not provided or was invalid.');
        }

        if (!isset($this->post['form_name']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST variables were not set.');
        }

        if (!in_array($this->post['form_name'], ['clear-logs']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST variables were invalid.');
        }

        return true;
    }

    /**
     * Parse the $_GET request variables and filter the orders for the requested page.
     *
     * @return array
     */
    private function parseGet(): array
    {
        // Prep the response
        $response =
        [
            'logs'          => $this->loadLogs(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        // If the orders are empty,
        // There's no need to check for max pages
        if (!empty($response['logs']))
        {
            $response['max_page'] = $this->loadLogs(true);
        }

        return $response;
    }

    /**
     * Check if the GET URL queries are either empty or set to defaults.
     *
     * @return bool
     */
    private function emptyQueries(): bool
    {
        $queries = $this->getQueries();

        return (
            $queries['page']   === 0 &&
            $queries['sort']   === 'newest' &&
            $queries['type']   === false &&
            $queries['date']   === false
        );
    }

    /**
     * Returns the requested GET queries with defaults.
     *
     * @return array
     */
    private function getQueries(): array
    {
        // Get queries
        $queries = $this->Request->queries();

        // Set defaults
        if (!isset($queries['page']))     $queries['page']   = 0;
        if (!isset($queries['sort']))     $queries['sort']   = 'newest';
        if (!isset($queries['type']))     $queries['type']   = false;
        if (!isset($queries['date']))     $queries['date']   = false;

        return $queries;
    }

    /**
     * Returns the list of orders for display.
     *
     * @param  bool      $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadLogs(bool $checkMaxPages = false)
    {
        // Get queries
        $queries = $this->getQueries();

        // Default operation values
        $page         = intval($queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = $queries['sort'];
        $perPage      = 200;
        $offset       = $page * $perPage;
        $type         = $queries['type'];
        $date         = $queries['date'];
        $output       = [];

        // Let get the error files
        $logs = Filesystem::list($this->Config->get('application.error_handler.log_path'), ['..', '.', '.DS_Store', '.gitignore']);

        // Filter so we are only returning 'all_errors' (no duplicates)
        foreach ($logs as $i => $name)
        {
            if (!Str::contains($name, 'all_errors'))
            {
                unset($logs[$i]);
            }
        }

        // First we need to sort the actual files
        usort($logs, function($a, $b) use ($sort)
        {
            $aDate = str_replace('_', '-', Str::getBeforeLastChar(Str::getBeforeLastChar($a, '_'), '_'));
            $bdate = str_replace('_', '-', Str::getBeforeLastChar(Str::getBeforeLastChar($b, '_'), '_'));

            if ($sort === 'oldest')
            {
                return strtotime($bdate) - strtotime($aDate);
            }
            elseif ($sort === 'newest')
            {
                return strtotime($aDate) - strtotime($bdate);
            }
        });

        // Read the log files and save each line
        foreach ($logs as $log)
        {
            $contents = trim(Filesystem::getContents($this->Config->get('application.error_handler.log_path') . '/' . $log));
            $blocks   = array_reverse(array_filter(array_map('trim', preg_split("#\n\s*\n#Uis", $contents))));

            if ($sort === 'oldest')
            {
                $blocks = array_reverse($blocks);
            }

            foreach ($blocks as $block)
            {
                $lines = explode("\n", $block);

                $errorType = $lines[1];

                $errorDate = trim(Str::getAfterFirstChar($lines[0], ':'));

                // Filter by error type
                if ($type === 'non404')
                {
                    if (Str::contains(strtolower($errorType), '404'))
                    {
                        continue;
                    }
                }

                // Filter by error type
                elseif ($type && !Str::contains(strtolower($errorType), $type))
                {
                    continue;
                }

                // Filter by date
                if ($date === 'today')
                {
                    // @todo - filter
                }

                foreach ($lines as $line)
                {
                   $output[] = trim($line);
                }

                $output[] = '';
                $output[] = '';
            }
        }

        if ($checkMaxPages)
        {
            return ceil(count($output) / $perPage);
        }

        $output = Arr::paginate($output, $page, $perPage);

        // Are we checking the pages ?
        if (isset($output[$page]))
        {
            return $output[$page];
        }

        return [];
    }
}
