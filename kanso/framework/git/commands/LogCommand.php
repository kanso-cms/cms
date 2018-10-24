<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands;

use kanso\framework\git\Command;

/**
 * Git log command.
 *
 * @see  https://git-scm.com/docs/git-log
 * @author Joe J. Howard
 */
class LogCommand extends Command
{
    /**
     * Magic method invoke.
     *
     * @param  array      $options Command options (optional) (default [])
     * @param  array      $params  Command params  (optional) (default [])
     * @return bool|array
     */
    public function __invoke(array $options = [], array $params = [])
    {
        // Resolve the options
        if (isset($options['limit']))
        {
            $options['max-count'] = $options['limit'];
            unset($options['limit']);
        }
        $options = array_merge(['max-count' => 10, 'numstat', 'format' => 'START_OF_GIT_LOG%n%H%n|__GIT_SPLIT__|%P%n|__GIT_SPLIT__|%cN%n|__GIT_SPLIT__|%cE%n|__GIT_SPLIT__|%cD%n|__GIT_SPLIT__|%B%n|END_OF_CODECOMET_GIT_LOG%n'], $options);

        // Run the command
        $output = $this->run('log', [$options, $params]);

        // Check if successfull
        if (!$this->is_successful()) return false;

        // Format the result
        $blocks  = array_filter(array_map('trim', explode('|END_OF_CODECOMET_GIT_LOG', $output)));
        $commits = [];

        foreach ($blocks as $i => $block)
        {
            $files = [];

            // The file stats may be on the next block
            // instead of this one
            if (isset($blocks[$i + 1]))
            {
                $nextBlock = $blocks[$i + 1];
                $nextLines = explode("\n", $nextBlock);

                // The file list must be on this block
                if (strpos($nextLines[0], 'START_OF_GIT_LOG') !== false)
                {
                    $lines = explode("\n", $block);

                    foreach ($lines as $s => $line)
                    {
                        if (strpos($line, 'START_OF_GIT_LOG') !== false)
                        {
                            break;
                        }

                        $files[] = $line;

                        unset($lines[$s]);
                    }

                    $blocks[$i] = implode("\n", $lines);
                }
                // The file list on the next block
                else
                {
                    foreach ($nextLines as $s => $nextLine)
                    {
                        if (strpos($nextLine, 'START_OF_GIT_LOG') !== false)
                        {
                            break;
                        }

                        $files[] = $nextLine;

                        unset($nextLines[$s]);
                    }

                    $blocks[$i + 1] = implode("\n", $nextLines);
                }
            }

            $block = $blocks[$i];
            if (empty($block))
            {
                continue;
            }

            $lineData = array_values(array_filter(array_map('trim', explode('|__GIT_SPLIT__|', ltrim($block, 'START_OF_GIT_LOG')))));

            if (count($lineData) < 5)
            {
                continue;
            }

            // Special case for a commit without a parent
            if (count($lineData) === 5)
            {
                array_splice($lineData, 1, 0, '');
            }

            $msg           = array_filter(array_map('trim', explode("\n", array_pop($lineData))));
            $title         = array_shift($msg);
            $description   = !empty($msg) ? implode("\n", $msg) : '';
            $deletions     = 0;
            $additions     = 0;
            $changes       = 0;
            $files_changed = 0;
            $commit_files  = [];

            if (!empty($files))
            {
                foreach ($files as $file)
                {
                    $file_stats = array_map('trim', explode("\t", $file));

                    if (count($file_stats) >= 3)
                    {
                        $is_bin = $file_stats[0] === '-' && $file_stats[1] === '-' ? true : false;
                        $dels   = intval(array_shift($file_stats));
                        $adds   = intval(array_shift($file_stats));
                        $name   = trim(implode("\t", $file_stats));
                        $commit_files[] = [
                            'path'      => $name,
                            'name'      => substr($name, strrpos($name, '/') + 1),
                            'additions' => $adds,
                            'deletions' => $deletions,
                            'binary'    => $is_bin,
                        ];
                        $deletions += $dels;
                        $additions += $adds;
                        $changes   += ($dels + $adds);
                        $files_changed += 1;
                    }
                }
            }

            $parents = explode(' ', $lineData[1]);

            // Is this a merge commit ?
            if (count($parents) === 2)
            {
                $last_parent = $parents[1];
                $parent_log  = $this->git->log(['limit' => 1], [$last_parent]);

                if ($parent_log && isset($parent_log[0]))
                {
                    $changes = $parent_log[0]['change_count'];
                    $files_changed = $parent_log[0]['file_count'];
                    $additions = $parent_log[0]['additions'];
                    $deletions = $parent_log[0]['deletions'];
                    $commit_files = $parent_log[0]['files'];
                }
            }
            $commits[] =
            [
                'hash'            => $lineData[0],
                'parent'          => $parents,
                'name'            => $lineData[2],
                'email'           => $lineData[3],
                'date'            => $lineData[4],
                'title'           => htmlentities($title),
                'description'     => htmlentities($description),
                'change_count'    => $changes,
                'file_count'      => $files_changed,
                'additions'       => $additions,
                'deletions'       => $deletions,
                'files'           => $commit_files,
            ];
        }

        return $commits;
    }

    /**
     * Run log and get raw output.
     *
     * @param  array        $options Command options (optional) (default [])
     * @param  array        $params  Command params  (optional) (default [])
     * @return false|string
     */
    public function raw($options =[], $params = [])
    {
        // Run the command
        $output = $this->run('log', [$options, $params]);

        // Check if successfull
        if (!$this->is_successful())
        {
            return false;
        }

        return $output;
    }
}
