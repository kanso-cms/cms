<?php

namespace Kanso\Utility;

/**
 * PHP Word Pluralizer
 * https://github.com/joey-j/PHP-Pluralizer
 *
 */
class Pluralize
{

	private static $word;
	private static $lowercase;
	private static $upperCase;
	private static $sentenceCase;
	private static $casing;
	private static $sibilants 	= ['x', 's', 'z', 's'];
    private static $vowels 		= ['a', 'e', 'i', 'o', 'u'];
    private static $consonants 	= ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'];

 	public static function convert($word, $count = 2) 
    {

        // Return the word if we don't need to pluralize
        if ($count === 1) return $word;

        // Set class variables for use
        self::$word 	 	= $word;
        self::$lowercase 	= strtolower($word);
        self::$upperCase 	= strtoupper($word);
        self::$sentenceCase = ucfirst($word);
        self::$casing 		= self::getCasing();
        
        // save some time in the case that singular and plural are the same
        if (self::isUncountable()) return $word;

        // check for irregular forms
        $irregular = self::isIrregular();
        if ($irregular) return self::toCasing($irregular, self::$casing);

        // nouns that end in -ch, x, s, z or s-like sounds require an es for the plural:
        if (in_array(self::suffix(self::$lowercase, 1), self::$sibilants) || (self::suffix(self::$lowercase, 2) === 'ch')) return self::toCasing($word . 'es', self::$casing);

        // Nouns that end in a vowel + y take the letter s:
        if (in_array(self::nthLast(self::$lowercase, 1), self::$vowels) && self::suffix(self::$lowercase, 1) === 'y') return self::toCasing($word . 's', self::$casing);

        // Nouns that end in a consonant + y drop the y and take ies:
        if (in_array(self::nthLast(self::$lowercase, 1), self::$consonants) && self::suffix(self::$lowercase, 1) === 'y') return self::toCasing(sliceFromEnd($word, 1) . 'ies', self::$casing);

        // Nouns that end in a consonant + o add s:
        if (in_array(self::nthLast(self::$lowercase, 1), self::$consonants) && self::suffix(self::$lowercase, 1) === 'o') return self::toCasing($word . 's', self::$casing);

        // Nouns that end in a vowel + o take the letter s:
        if (in_array(self::nthLast(self::$lowercase, 1), self::$vowels) && self::suffix(self::$lowercase, 1) === 'o') return self::toCasing($word . 's', self::$casing);

        // irregular suffixes that cant be pluralized
        if (self::suffix(self::$lowercase, 4) === 'ness' || self::suffix(self::$lowercase, 3) === 'ess') return $word;

        // Lastly, change the word based on suffix rules
        $pluralized = self::autoSuffix(self::$lowercase);

        if ($pluralized) return self::toCasing(self::sliceFromEnd($word, $pluralized[0]) . $pluralized[1], self::$casing);

        return self::$word.'s';
    }

    private static function isUncountable() 
    {
       	$uncountable = [
            'sheep',
            'fish',
            'deer',
            'series',
            'species',
            'money',
            'rice',
            'information',
            'equipment',
            'bison',
            'buffalo',
            'duck',
            'pike',
            'plankton',
            'salmon',
            'squid',
            'swine',
            'trout',
            'moose',
            'aircraft',
            'you',
            'pants',
            'shorts',
            'eyeglasses',
            'scissors',
            'offspring',
            'eries',
            'premises',
            'kudos',
            'corps',
            'heep',
        ];
        return in_array(self::$lowercase, $uncountable);
    }

    private static function isIrregular() 
    {
        $irregular = [
            'addendum'=> 'addenda',
            'alga'=> 'algae',
            'alumna'=> 'alumnae',
            'alumnus'=> 'alumni',
            'analysis'=> 'analyses',
            'antenna'=> 'antennae',
            'apparatus'=> 'apparatuses',
            'appendix'=> 'appendices',
            'axis'=> 'axes',
            'bacillus'=> 'bacilli',
            'bacterium'=> 'bacteria',
            'basis'=> 'bases',
            'beau'=> 'beaux',
            'kilo'=> 'kilos',
            'bureau'=> 'bureaus',
            'bus'=> 'busses',
            'cactus'=> 'cacti',
            'calf'=> 'calves',
            'child'=> 'children',
            'corps'=> 'corps',
            'corpus'=> 'corpora',
            'crisis'=> 'crises',
            'criterion'=> 'criteria',
            'curriculum'=> 'curricula',
            'datum'=> 'data',
            'deer'=> 'deer',
            'die'=> 'dice',
            'dwarf'=> 'dwarves',
            'diagnosis'=> 'diagnoses',
            'echo'=> 'echoes',
            'elf'=> 'elves',
            'ellipsis'=> 'ellipses',
            'embargo'=> 'embargoes',
            'emphasis'=> 'emphases',
            'erratum'=> 'errata',
            'fireman'=> 'firemen',
            'fish'=> 'fish',
            'focus'=> 'focuses',
            'foot'=> 'feet',
            'formula'=> 'formulas',
            'fungus'=> 'fungi',
            'genus'=> 'genera',
            'goose'=> 'geese',
            'half'=> 'halves',
            'hero'=> 'heroes',
            'hippopotamus'=> 'hippopotami',
            'hoof'=> 'hooves',
            'hypothesis'=> 'hypotheses',
            'index'=> 'indices',
            'knife'=> 'knives',
            'leaf'=> 'leaves',
            'life'=> 'lives',
            'loaf'=> 'loaves',
            'louse'=> 'lice',
            'man'=> 'men',
            'matrix'=> 'matrices',
            'means'=> 'means',
            'medium'=> 'media',
            'memorandum'=> 'memoranda',
            'millennium'=> 'millenniums',
            'moose'=> 'moose',
            'mosquito'=> 'mosquitoes',
            'mouse'=> 'mice',
            'my'=> 'our',
            'nebula'=> 'nebulae',
            'neurosis'=> 'neuroses',
            'nucleus'=> 'nuclei',
            'neurosis'=> 'neuroses',
            'nucleus'=> 'nuclei',
            'oasis'=> 'oases',
            'octopus'=> 'octopi',
            'ovum'=> 'ova',
            'ox'=> 'oxen',
            'paralysis'=> 'paralyses',
            'parenthesis'=> 'parentheses',
            'person'=> 'people',
            'phenomenon'=> 'phenomena',
            'potato'=> 'potatoes',
            'radius'=> 'radii',
            'scarf'=> 'scarfs',
            'self'=> 'selves',
            'series'=> 'series',
            'sheep'=> 'sheep',
            'shelf'=> 'shelves',
            'scissors'=> 'scissors',
            'species'=> 'species',
            'stimulus'=> 'stimuli',
            'stratum'=> 'strata',
            'syllabus'=> 'syllabi',
            'symposium'=> 'symposia',
            'synthesis'=> 'syntheses',
            'synopsis'=> 'synopses',
            'tableau'=> 'tableaux',
            'that'=> 'those',
            'thesis'=> 'theses',
            'thief'=> 'thieves',
            'this'=> 'these',
            'tomato'=> 'tomatoes',
            'tooth'=> 'teeth',
            'torpedo'=> 'torpedoes',
            'vertebra'=> 'vertebrae',
            'veto'=> 'vetoes',
            'vita'=> 'vitae',
            'watch'=> 'watches',
            'wife'=> 'wives',
            'wolf'=> 'wolves',
            'woman'=> 'women',
            'is'=> 'are',
            'was'=> 'were',
            'he'=> 'they',
            'she'=> 'they',
            'i'=> 'we',
            'zero'=> 'zeroes',
      	];

        if (isset($irregular[self::$lowercase])) return $irregular[self::$lowercase];

        return false;
    }

    private static function autoSuffix() 
    {

        $suffix1 = self::suffix(self::$lowercase, 1);
        $suffix2 = self::suffix(self::$lowercase, 2);
        $suffix3 = self::suffix(self::$lowercase, 3);

        if (self::suffix(self::$lowercase, 4) === 'zoon') return [4, 'zoa'];

        if ($suffix3 === 'eau') return [3, 'eaux'];
        if ($suffix3 === 'ieu') return [3, 'ieux'];
        if ($suffix3 === 'ion') return [3, 'ions'];
        if ($suffix3 === 'oof') return [3, 'ooves'];

        if ($suffix2 === 'an') return [2, 'en'];
        if ($suffix2 === 'ch') return [2, 'ches'];
        if ($suffix2 === 'en') return [2, 'ina'];
        if ($suffix2 === 'ex') return [2, 'exes'];
        if ($suffix2 === 'is') return [2, 'ises'];
        if ($suffix2 === 'ix') return [2, 'ices'];
        if ($suffix2 === 'nx') return [2, 'nges'];
        if ($suffix2 === 'nx') return [2, 'nges'];
        if ($suffix2 === 'fe') return [2, 'ves'];
        if ($suffix2 === 'on') return [2, 'a'];
        if ($suffix2 === 'sh') return [2, 'shes'];
        if ($suffix2 === 'um') return [2, 'a'];
        if ($suffix2 === 'us') return [2, 'i'];
        if ($suffix2 === 'x') return [1, 'xes'];
        if ($suffix2 === 'y') return [1, 'ies'];

        if ($suffix1 === 'a') return [1, 'ae'];
        if ($suffix1 === 'o') return [1, 'oes'];
        if ($suffix1 === 'f') return [1, 'ves'];

        return false;
    }

    private static function getCasing() 
    {
        $casing = 'lower';
        $casing = self::$lowercase === self::$word ? 'lower' : $casing;
        $casing = self::$upperCase === self::$word ? 'upper' : $casing;
        $casing = self::$sentenceCase === self::$word ? 'sentence' : $casing;
        return $casing;
    }

    private static function toCasing($word, $casing) {
        if ($casing === 'lower') return strtolower($word);
        if ($casing === 'upper') return strtoupper($word);
        if ($casing === 'sentence') return ucfirst($word);
        return $word;
    }

    private static function suffix($word, $count) {
    	return substr($word, count($word)-1 - $count);
    }

    private static function nthLast($word, $count) {
    	implode('', array_reverse(str_split($word)))[$count];
    }

    private static function sliceFromEnd($word, $count) {
    	return substr($word, 0, count($word)-1 - $count);
    }
}