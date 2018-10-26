 * Parse diff parse.
     * Get HTML diff meta stats.
    public static function metaStat(array $diff, string $tooltipdir = 'e', string $classes = '')
            elseif ($diff['file_removed'] === true)
        // Special case for adding a new empty file
        // Special case for deleting an empty file
        elseif ($additions > 5 && $deletions === 0)
        elseif ($deletions <= 5 && $additions === 0)
        elseif ($deletions > 5 && $additions === 0)
        elseif ($additions === $deletions)
        elseif ($additions > $deletions)
            elseif ($additions > 50 && $diff > 20)
        elseif ($deletions > $additions)
            elseif ($deletions > 50 && $diff > 20)
    public static function parse($output, $git, $sha = null, $limit = true)
        // Split the files into files -> lines
        // Total diff array
        // Loop the files

            // Diff template
            $isBinary = (isset($file[2]) && strpos($file[2], 'Binary files') !== false || (isset($file[3]) && strpos($file[3], 'Binary files') !== false));

            // Counters
            $current_hunk = 0;

            // Used to skip the headers in the git patches
            // Loop the file lines

                // Diff header

                    elseif ($isImage && strpos($file[1], 'index') !== false)
                    {

                // If we havent picked up a file diff carry on calmly
                // Diff starts
                    // Prepare for diffs
                    // Prepare for diffs
                        'type'   => 'header',
                        'a_line' => null,
                        'b_line' => null,
                        'value'  => "@@ -$matches[1],$matches[2] +$matches[3],$matches[4] @@ " . htmlentities($matches[5]),

                    // Take down the line numbers to use

                // Padding lines that we dont really care about
                // Removed lines

                        'type'   => 'deletion',
                        'a_line' => $b_line++,
                        'b_line' => null,
                        'type'   => 'addition',
                        'a_line' => null,
                        'b_line' => $a_line++,

                        'type'   => 'context',
                        'a_line' => $b_line++,
                        'b_line' => $a_line++,
            // Blank new files
            // Or blank deleted files
                elseif ($fileArray['file_removed'] === true)
            // Don't add lines that are not a file

    private static function splitFiles($raw_diff)
    {


        return $files;

        elseif ($diff['file_removed'] === true)

                    <span class="border-wrap danger"><img src="/raw/' . "$slug/$oldSha/$filename" . '"></span>
                    <span class="border-wrap success"><img src="/raw/' . "$slug/$sha/$filename" . '"></span>

                elseif ($diff['file_removed'] === true)
                    <span class="font-400 font-14">' . $msg . '</span>

                    $html .= '<td class="line-content"><span class="line-code-inner">' . htmlentities($change['value']) . '</span></td>';
                    $html .= '<td class="line-num" data-line-number="' . $change['a_line'] . '"></td>';
                    $html .= '<td class="line-num" data-line-number="' . $change['b_line'] . '"></td>';
                    $html .= '<td class="line-content"><span class="line-code-inner">' . htmlentities($change['value']) . '</span></td>';
                elseif($change['type'] == 'addition')
                    $html .= '<td class="line-num" data-line-number="' . $change['b_line'] . '"></td>';
                    $html .= '<td class="line-content"><span class="line-code-inner">' . htmlentities($change['value']) . '</span></td>';
                elseif($change['type'] == 'deletion')
                    $html .= '<td class="line-num" data-line-number="' . $change['a_line'] . '"></td>';
                    $html .= '<td class="line-content deletion"><span class="line-code-inner">' . htmlentities($change['value']) . '</span></td>';
        // Should the diff be surpressed
            $id = preg_replace('/[^a-zA-Z]/', '', $diff['filename']);
                    <span class="font-400 font-14">We\'ve hidden this diff because it\'s really big. <a href="#" class="js-show-diff" data-target="' . $id . '">Click to show</a>.</span>
                <table class="diff-table inline hidden" id="diff-' . $id . '"><tbody>' . $html;
            return '<table class="diff-table inline"><tbody>' . $html;
    public static function noDiffMessage()
}