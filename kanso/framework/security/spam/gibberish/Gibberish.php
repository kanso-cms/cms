<?php

namespace kanso\framework\security\spam\gibberish;

/**
 * A small program to determine if text content contains gibberish.
 * Translated into PHP by Oliver Lillie originally written in Python by Rob Renaud.
 * Copyright Oliver Lillie 2011.
 *
 * Original Python Author: Rob Renaud <rrenaud@gmail.com> https://github.com/rrenaud/Gibberish-Detector
 * PHP Author: Oliver Lillie <buggedcom@gmail.com> https://github.com/buggedcom/Gibberish-Detector-PHP
 *
 * Tests text content for gibberish input such as
 * tapoktrpasawe
 * qweasd qwa as
 * aıe qwo ıak kqw
 * qwe qwe qwe a
 *
 * @link http://stackoverflow.com/questions/6297991/is-there-any-way-to-detect-strings-like-putjbtghguhjjjanika
 * @link https://github.com/rrenaud/Gibberish-Detector
 * @link http://en.wikipedia.org/wiki/Markov_chain
 * @param  string $text    The text to check.
 * @param  array  $options
 * @return mixed
 * @author Oliver Lillie
 * @author Rob Renaud Python implementation
 */
class Gibberish
{

    protected $_accepted_characters = 'abcdefghijklmnopqrstuvwxyz ';

    protected $lib_path;

    /**
     * Constructor.
     *
     * @access public
     * @param string $lib_path Path to library
     */
    public function __construct(string $lib_path)
    {
        $this->lib_path = $lib_path;
    }

    public function test(string $text, $raw = false)
    {
        $trained_library = unserialize(file_get_contents($this->lib_path));

        if(is_array($trained_library) === false)  return -1;

        $value = $this->_averageTransitionProbability($text, $trained_library['matrix']);

        if($raw === true) return $value;

        if($value <= $trained_library['threshold']) return true;

        return false;

    }

    protected function _normalise($line)
    {
        //  Return only the subset of chars from accepted_chars.
        //  This helps keep the  model relatively small by ignoring punctuation,
        // infrequenty symbols, etc.
        return preg_replace('/[^a-z\ ]/', '', strtolower($line));
    }

    public function train($big_text_file, $good_text_file, $bad_text_file, $lib_path)
    {

        if(is_file($big_text_file) === false || is_file($good_text_file) === false || is_file($bad_text_file) === false) return false;

        $k   = strlen($this->_accepted_characters);
        $pos = array_flip(str_split($this->_accepted_characters));

        // Assume we have seen 10 of each character pair.  This acts as a kind of
        // prior or smoothing factor.  This way, if we see a character transition
        // live that we've never observed in the past, we won't assume the entire
        // string has 0 probability.
        $log_prob_matrix = [];
        $range           = range(0, count($pos)-1);

        foreach ($range as $index1) {
            $array = [];
            foreach ($range as $index2) {
                $array[$index2] = 10;
            }
            $log_prob_matrix[$index1] = $array;
        }

        // Count transitions from big text file, taken
        // from http:#norvig.com/spell-correct.html
        $lines = file($big_text_file);

        foreach ($lines as $line) {

            // Return all n grams from l after normalizing
            $filtered_line = str_split($this->_normalise($line));
            $a = false;
            foreach ($filtered_line as $b)
            {
                if($a !== false)
                {
                    $log_prob_matrix[$pos[$a]][$pos[$b]] += 1;
                }
                $a = $b;
            }

        }
        unset($lines, $filtered_line);

        // Normalize the counts so that they become log probabilities.
        // We use log probabilities rather than straight probabilities to avoid
        // numeric underflow issues with long texts.
        // This contains a justification:
        // http:#squarecog.wordpress.com/2009/01/10/dealing-with-underflow-in-joint-probability-calculations/
        foreach ($log_prob_matrix as $i => $row) {
            $s = (float) array_sum($row);
            foreach($row as $k=>$j)
            {
                $log_prob_matrix[$i][$k] = log($j/$s);
            }
        }

        // Find the probability of generating a few arbitrarily choosen good and
        // bad phrases.
        $good_lines = file($good_text_file);
        $good_probs = [];
        foreach ($good_lines as $line) {
            array_push($good_probs, $this->_averageTransitionProbability($line, $log_prob_matrix));
        }

        $bad_lines = file($bad_text_file);
        $bad_probs = [];
        foreach ($bad_lines as $line) {
            array_push($bad_probs, $this->_averageTransitionProbability($line, $log_prob_matrix));
        }

        // Assert that we actually are capable of detecting the junk.
        $min_good_probs = min($good_probs);
        $max_bad_probs = max($bad_probs);

        if($min_good_probs <= $max_bad_probs) return false;

        // And pick a threshold halfway between the worst good and best bad inputs.
        $threshold = ($min_good_probs + $max_bad_probs) / 2;

        // save matrix
        return file_put_contents($lib_path, serialize([
            'matrix' => $log_prob_matrix,
            'threshold' => $threshold,
        ])) > 0;
    }

    public function _averageTransitionProbability($line, $log_prob_matrix)
    {

        // Return the average transition prob from line through log_prob_mat.
        $log_prob = 1.0;
        $transition_ct = 0;

        $pos = array_flip(str_split($this->_accepted_characters));
        $filtered_line = str_split($this->_normalise($line));
        $a = false;

        foreach ($filtered_line as $b) {
            if($a !== false)
            {
                $log_prob += $log_prob_matrix[$pos[$a]][$pos[$b]];
                $transition_ct += 1;
            }
            $a = $b;
        }

        // The exponentiation translates from log probs to probs.
        return exp($log_prob / max($transition_ct, 1));
    }

}
