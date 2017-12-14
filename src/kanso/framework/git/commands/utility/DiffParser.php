<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands\utility;

/**
 * Parse diff parse
 *
 * @see  https://git-scm.com/docs/git-diff
 * @author Joe J. Howard
 */
class DiffParser
{
    /**
     * Get HTML diff meta stats
     *
     * @param  array  $diff       Pre-parsed output from diff command
     * @param  string $tooltipdir Tooltip suffix direction (optional) (default 'e')
     * @param  string $classes    Classnames for meta elements (optional) (default '')
     * @return string
     */
    public static function metaStat(array $diff, string $tooltipdir = 'e', string $classes = '') 
    {
        // Binary files
        if ($diff['is_binary'] === true)
        {
            if ($diff['file_added'] === true)
            {
                return "
                <span class=\"diff-meta $classes tooltipped tooltipped-$tooltipdir\" data-tooltip=\"Binary file added\">
                    <span class=\"code-font font-11 meta-cnt\">BIN</span>
                    <span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span>
                </span>
                ";
            }
            else if ($diff['file_removed'] === true)
            {
                return "
                <span class=\"diff-meta $classes tooltipped tooltipped-$tooltipdir\" data-tooltip=\"Binary file removed\">
                    <span class=\"code-font font-11 meta-cnt\">BIN</span>
                    <span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span>
                </span>
                ";
            }
            else
            {
                return "
                <span class=\"diff-meta $classes tooltipped tooltipped-$tooltipdir\" data-tooltip=\"Binary file modified\">
                    <span class=\"code-font font-11 meta-cnt\">BIN</span>
                    <span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span>
                    <span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span>
                    <span class=\"meta unused\">▪</span>
                </span>
                ";
            }
        }

        # Special case for adding a new empty file
        if ($diff['file_added'] === true && empty($diff['chunks']))
        {
            return "
            <span class=\"diff-meta $classes tooltipped tooltipped-$tooltipdir\" data-tooltip=\"Blank file added\">
                <span class=\"code-font font-11 meta-cnt\">1</span>
                <span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span><span class=\"meta color-success\">+</span>
            </span>
            ";
        }

        # Special case for deleting an empty file
        if ($diff['file_removed'] === true && empty($diff['chunks']))
        {
            return "
            <span class=\"diff-meta $classes tooltipped tooltipped-$tooltipdir\" data-tooltip=\"Blank file deleted\">
                <span class=\"code-font font-11 meta-cnt\">1</span>
                <span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span><span class=\"meta color-danger\">-</span>
            </span>
            ";
        }


        $additions = $diff['additions'];
        $deletions = $diff['deletions'];
        $html      = '';
        $total     = $additions + $deletions;
        $delCnt    = 0;
        $addCnt    = 0;

        // Full additions
        if ($additions <= 5 && $deletions === 0)
        {
            $addCnt = $additions;
            $delCnt = 0;
        }
        // Full additions
        else if ($additions > 5 && $deletions === 0)
        {
            $addCnt = 5;
            $delCnt = 0;
        }

        // Full deletions
        else if ($deletions <= 5 && $additions === 0)
        {
            $addCnt = 0;
            $delCnt = $deletions;
        }
        // Full deletions
        else if ($deletions > 5 && $additions === 0)
        {
            $addCnt = 0;
            $delCnt = 5;
        }

        // 50-50
        else if ($additions === $deletions)
        {
            if ($additions === 1)
            {
                $delCnt  = 1;
                $addCnt  = 1;
            }
            else
            {
                $delCnt  = 2;
                $addCnt  = 2;
            }
        }

        // More Additions
        else if ($additions > $deletions)
        {
            $diff = $additions - $deletions;
            if ($additions <= 3)
            {
                $addCnt = $additions;
                $delCnt = $deletions;
            }
            else if ($additions > 50 && $diff > 20)
            {
                $addCnt = 3;
                $delCnt = 2;
            }
            else
            {
                $addCnt = 2;
                $delCnt = 1;
            }
        }

        // More deletions
        else if ($deletions > $additions)
        {
            $diff = $deletions - $additions;
            if ($deletions <= 3)
            {
                $addCnt = $additions;
                $delCnt = $deletions;
            }
            else if ($deletions > 50 && $diff > 20)
            {
                $delCnt = 3;
                $addCnt = 2;
            }
            else
            {
                $delCnt = 2;
                $addCnt = 1;
            }
        }

        for ($i = 0; $i < $addCnt; $i++)
        {
            $html .= '<span class="meta color-success">+</span>';
        }

        for ($i = 0; $i < $delCnt; $i++)
        {
            $html .= '<span class="meta color-danger">-</span>';
        }

        $remains = 5 - $addCnt - $delCnt;
        for ($i = 0; $i < $remains; $i++)
        {
            $html .= '<span class="meta unused">▪</span>';
        }

        $addition = \Framework\Utility\Pluralize::convert('addition', $additions);
        $deletion = \Framework\Utility\Pluralize::convert('deletion', $deletions);
        return "
            <span class=\"diff-meta $classes tooltipped tooltipped-$tooltipdir\" data-tooltip=\"$additions $addition & $deletions $deletion\">
                <span class=\"code-font font-11 meta-cnt\">$total</span>
                $html
            </span>
        ";
    }

    public static function parse($output, $git, $sha = null, $limit = true) 
    {
        # Split the files into files -> lines
        $files = self::splitFiles($output);

        # Total diff array
        $diffs = [
            'total_changes'   => 0,
            'total_additions' => 0,
            'total_deletions' => 0,
            'files'           => [],
            'reached_max'     => false,
        ];

        # Loop the files
        foreach ($files as $i => $file)
        {

            if ($limit)
            {
                if (count($diffs['files']) > 50)
                {
                    $diffs['reached_max'] = true;
                }
            }
            
            # Diff template
            $fileArray = [
                'oldSha'       => null,
                'is_binary'    => false,
                'file_removed' => false,
                'file_added'   => false,
                'filename'     => '',
                'changes'      => 0,
                'additions'    => 0,
                'deletions'    => 0,
                'chunks'       => [],
            ];

            // Check for binary files
            $isBinary = ( isset($file[2]) && strpos($file[2], 'Binary files') !== false || ( isset($file[3]) && strpos($file[3], 'Binary files') !== false) );
            if ($isBinary)
            {
                $fileArray['is_binary']    = true;
                $fileArray['file_added']   = strpos($file[1], 'new file mode') !== false;
                $fileArray['file_removed'] = strpos($file[1], 'deleted file mode') !== false;
            }
            
            # Counters
            $current_hunk = 0;       
            $b_line = 0;
            $a_line = 0;
        
            # Used to skip the headers in the git patches
            $indiff = false;

            # Loop the file lines
            foreach ($file as $line)
            {
                
                # Diff header
                if (preg_match("/^diff --git SRC\/([^.]+\.\w+) DST\/([^.]+\.\w+)$/", $line, $matches))
                {

                    $fileArray['file_added']   = strpos($file[1], 'new file mode') !== false;
                    $fileArray['file_removed'] = strpos($file[1], 'deleted file mode') !== false;

                    $fileArray['filename'] =  $matches[1];
                    
                    // Reset file counter and maintain diff
                    $current_hunk = 0;
                    $indiff = true;

                    $ext     = substr($matches[1], strrpos($matches[1], '.') + 1);
                    $isFont  = in_array($ext, ['eot', 'ttf', 'woff', 'ttf', 'woff2', 'otf']);
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'gif', 'png']);


                    // Check for fonts
                    if ($fileArray['is_binary'] === false)
                    {
                        if ($isFont)
                        {
                            $fileArray['is_binary']    = true;
                            $fileArray['file_added']   = strpos($file[1], 'new file mode') !== false;
                            $fileArray['file_removed'] = strpos($file[1], 'deleted file mode') !== false;
                        }
                    }
                    // Binary files that are images need to the old image
                    // to compare - find the parent sha from the previous commit
                    else if ($isImage && strpos($file[1], 'index') !== false)
                    {                       
                        $commit = $git->log(['reverse' => true, 'limit' => 1], [$sha, '--', $matches[1]]);
                        if ($commit)
                        {
                            $fileArray['oldSha'] = $commit[0]['parent'];
                        }
                    }
                    continue;
                }
                
                # If we havent picked up a file diff carry on calmly
                if (!$indiff)
                {
                    continue;
                }

                # Diff starts
                if (preg_match('#^@@ -([0-9]+),?([0-9]+)? \+([0-9]+),?([0-9]+)? @@ ?(.*)#', $line, $matches))
                {
                    

                    # Prepare for diffs
                    $current_hunk++;

                    # Prepare for diffs
                    $fileArray['chunks'][$current_hunk-1][] = [
                        'type'   => 'header', 
                        'a_line' => null, 
                        'b_line' => null, 
                        'value'  => "@@ -$matches[1],$matches[2] +$matches[3],$matches[4] @@ ".htmlentities($matches[5]),
                    ];
                    
                    # Take down the line numbers to use
                    $b_line = (int) $matches[1];
                    $a_line = (int) $matches[3];
                    
                    continue;
                }

                # Padding lines that we dont really care about
                if (preg_match('#^[+-]{3,3}#', $line))
                {
                    continue;
                }

                # Removed lines
                if (preg_match('#^-(.*)$#', $line, $matches))
                {
                    $diffs['total_deletions'] += 1;
                    $diffs['total_changes']   += 1;
                    $fileArray['deletions']   += 1;
                    $fileArray['changes']     += 1;
                    
                    $fileArray['chunks'][$current_hunk-1][] = [
                        'type'   => 'deletion', 
                        'a_line' => $b_line++, 
                        'b_line' => null, 
                        'value'  => $matches[1],
                    ];
                    continue;
                }
                // Additional lines
                if (preg_match('#^\+(.*)$#', $line, $matches))
                {
                    $diffs['total_additions'] += 1;
                    $diffs['total_changes']   += 1;
                    $fileArray['additions']   += 1;
                    $fileArray['changes']     += 1;

                    $fileArray['chunks'][$current_hunk-1][] = [
                        'type'   => 'addition', 
                        'a_line' => null, 
                        'b_line' => $a_line++, 
                        'value'  => $matches[1],
                    ];
                    continue;
                }
                // Context lines
                if (preg_match('#^\s(.*)$#', $line, $matches))
                {
                    
                    $fileArray['chunks'][$current_hunk-1][] = [
                        'type'   => 'context', 
                        'a_line' => $b_line++, 
                        'b_line' => $a_line++, 
                        'value'  => $line,
                    ];
                    continue;
                }

            }

            # Blank new files
            # Or blank deleted files
            if ($fileArray['changes'] === 0)
            {
                if ($fileArray['file_added'] === true)
                {
                    $fileArray['additions'] = 1;
                    $fileArray['changes']   = 1;
                    $diffs['total_additions'] += 1;
                    $diffs['total_changes']   += 1;
                }
                else if ($fileArray['file_removed'] === true)
                {
                    $fileArray['deletions'] = 1;
                    $fileArray['changes']   = 1;
                    $diffs['total_deletions'] += 1;
                    $diffs['total_changes']   += 1;
                }
            }


            # Don't add lines that are not a file
            if ($fileArray['filename'] !== '') $diffs['files'][] = $fileArray;
            
        }

        return $diffs;
    }

    private static function splitFiles($raw_diff) 
    {        
        $files  = [];
        $buffer = [];
    
        foreach(preg_split("/(\r?\n)/", $raw_diff) as $line)
        {
            if (stripos($line, 'diff --git') === 0)
            {
                if (!empty($buffer))
                {
                    $files[] = $buffer;
                    $buffer  = [];
                }
            }
            $buffer[] = $line;
        }
    
        $files[] = $buffer;

        return $files;    
    }

    public static function renderImage($diff, $sha, $slug)
    {

        $filename = $diff['filename'];

        if ($diff['file_added'] === true)
        {
            
            return "
            <div class=\"blob-image-wrapper\">
                <div class=\"image\">
                    <span class=\"border-wrap success \"><img src=\"/raw/$slug/$sha/$filename\"></span>
                </div>
            </div>
            ";


        }
        else if ($diff['file_removed'] === true)
        {
           return '
            <div class="pad-20 text-center diff-message">
                <div class="row">
                    <span class="line-icon line-icon-image_photo_file color-grey icon-xl"></span>
                </div>
                <span class="font-400 font-14">Deleted image file not rendered.</span>
            </div>
            ';
        }
        
        if (!$diff['oldSha'])
        {
            return '
            <div class="pad-20 text-center diff-message">
                <div class="row">
                    <span class="line-icon line-icon-image_photo_file color-grey icon-xl"></span>
                </div>
                <span class="font-400 font-14">Binary file modified not shown.</span>
            </div>
            ';
        }

        $oldSha = $diff['oldSha'];

        return '

        <div class="img-diff-container js-img-diff-container two-up">
            
            <div class="blob-image-wrapper two-up js-two-up active">
                <div class="image">
                    <span class="border-wrap danger"><img src="/raw/'."$slug/$oldSha/$filename".'"></span>
                </div>
                <div class="image">
                    <span class="border-wrap success"><img src="/raw/'."$slug/$sha/$filename".'"></span>
                </div>
            </div>

        </div>
        ';

    }
    
    public static function render($diff, $sha = null, $slug = null, $exapandable = true)
    {

        $html   = '';
        $length = 0;

        // Binary file
        if ($diff['is_binary'] === true)
        {
            $ext     = substr($diff['filename'], strrpos($diff['filename'], '.') + 1);
            $isImage = in_array($ext, ['jpg', 'jpeg', 'gif', 'png']);
            if ($isImage && $sha && $slug)
            {
                return self::renderImage($diff, $sha, $slug);
            }
            else
            {
                $msg = 'Binary file not shown.';
                if ($diff['file_added'] === true)
                {
                    $msg = 'Binary file added not shown.';
                }
                else if ($diff['file_removed'] === true)
                {
                    $msg = 'Binary file removed not shown.';
                }
                else
                {
                    $msg = 'Binary file modified not shown.';
                }
                return '
                <div class="pad-20 text-center diff-message">
                    <div class="row">
                        <span class="line-icon line-icon-file_code_programming_dev_binary color-grey icon-xl"></span>
                    </div>
                    <span class="font-400 font-14">'.$msg.'</span>
                </div>
                ';
            }
        }

        $lines = 0;
        foreach($diff['chunks'] as $i => $blocks)
        {
            
            foreach($blocks as $change)
            {
                $length += strlen($change['value']);
                if ($length > 100000)
                {
                return '
                    <div class="pad-20 text-center diff-message">
                        <div class="row">
                            <span class="line-icon line-icon-document_file_delete_remove_error color-grey icon-xl"></span>
                        </div>
                        <span class="font-400 font-14">Sorry this diff is too large to display.</span>
                    </div>
                    ';
                }

                // Header text
                if($change['type'] == 'header')
                {
                    $html .= '<tr class="line header">';
                    $html .= '<td class="line-num empty-cell" data-line-number="..."></td>';
                    $html .= '<td class="line-num empty-cell" data-line-number="..."></td>';
                    $html .= '<td class="line-content"><span class="line-code-inner">'.htmlentities($change['value']).'</span></td>';
                    $html .= '</tr>';
                }

                // Equal changes should be shown on both sides of the diff
                if($change['type'] == 'context')
                {
                    $html .= '<tr class="line context">';
                    $html .= '<td class="line-num" data-line-number="'.$change['a_line'].'"></td>';
                    $html .= '<td class="line-num" data-line-number="'.$change['b_line'].'"></td>';
                    $html .= '<td class="line-content"><span class="line-code-inner">'.htmlentities($change['value']).'</span></td>';
                    $html .= '</tr>';
                }
                // Added lines only on the right side
                else if($change['type'] == 'addition')
                {
                    $html .= '<tr class="line addition">';
                    $html .= '<td class="line-num empty-cell">&nbsp;</td>';
                    $html .= '<td class="line-num" data-line-number="'.$change['b_line'].'"></td>';
                    $html .= '<td class="line-content"><span class="line-code-inner">'.htmlentities($change['value']).'</span></td>';
                    $html .= '</tr>';
                }
                // deleted lines only on the left side
                else if($change['type'] == 'deletion')
                {
                    $html .= '<tr class="line deletion">';
                    $html .= '<td class="line-num" data-line-number="'.$change['a_line'].'"></td>';
                    $html .= '<td class="line-num empty-cell">&nbsp;</td>';
                    $html .= '<td class="line-content deletion"><span class="line-code-inner">'.htmlentities($change['value']).'</span></td>';
                    $html .= '</tr>';
                }
                $lines++;
            }
        }
        $html .= '</tbody></table>';

        # Should the diff be surpressed
        if (($length > 5000 || $lines > 100) && $exapandable)
        {
            $id = preg_replace("/[^a-zA-Z]/", '', $diff['filename']);
            return '
                <div class="pad-20 text-center diff-message">
                    <div class="row">
                        <span class="line-icon line-icon-lightbulb color-grey icon-xl"></span>
                    </div>
                    <span class="font-400 font-14">We\'ve hidden this diff because it\'s really big. <a href="#" class="js-show-diff" data-target="'.$id.'">Click to show</a>.</span>
                </div>
                <table class="diff-table inline hidden" id="diff-'.$id.'"><tbody>'.$html;
        }
        else
        {
            return '<table class="diff-table inline"><tbody>'.$html;
        }

        return $html;
    }

    public static function noDiffMessage() 
    {
        return '
        <div class="pad-20 text-center diff-message">
            <div class="row">
                <span class="line-icon line-icon-document_file_delete_remove_error color-grey icon-xl"></span>
            </div>
            <span class="font-400 font-14">There are no changes to show.</span>
        </div>
        ';
    }

}