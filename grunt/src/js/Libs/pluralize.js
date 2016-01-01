// ##############################################################################
// FILE: Libs/pluralize.js
// ##############################################################################

/* global define */

(function(root, pluralize) {
    // Browser global.
    root.pluralize = pluralize();
})(this, function() {


    function pluralize(word, count) {

        count = (typeof count === 'undefined' ? 2 : count);

        // Return the word if we don't need to pluralize
        if (count === 1) return word;

        // Set class variables for use
        pluralize.word = word;
        pluralize.lowercase = word.toLowerCase();
        pluralize.upperCase = word.toUpperCase();
        pluralize.sentenceCase = ucfirst(word);
        pluralize.casing = getCasing();
        pluralize.sibilants = ['x', 's', 'z', 's'];
        pluralize.vowels = ['a', 'e', 'i', 'o', 'u'];
        pluralize.consonants = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'];

        // save some time in the case that singular and plural are the same
        if (isUncountable()) return word;

        // check for irregular forms
        var irregular = isIrregular();
        if (irregular) return toCasing(irregular, pluralize.casing);

        // nouns that end in -ch, x, s, z or s-like sounds require an es for the plural:
        if (in_array(suffix(pluralize.lowercase, 1), pluralize.sibilants) || (suffix(pluralize.lowercase, 2) === 'ch')) return toCasing(word + 'es', pluralize.casing);

        // Nouns that end in a vowel + y take the letter s:
        if (in_array(nthLast(pluralize.lowercase, 1), pluralize.vowels) && suffix(pluralize.lowercase, 1) === 'y') return toCasing(word + 's', pluralize.casing);

        // Nouns that end in a consonant + y drop the y and take ies:
        if (in_array(nthLast(pluralize.lowercase, 1), pluralize.consonants) && suffix(pluralize.lowercase, 1) === 'y') return toCasing(sliceFromEnd(word, 1) + 'ies', pluralize.casing);

        // Nouns that end in a consonant + o add s:
        if (in_array(nthLast(pluralize.lowercase, 1), pluralize.consonants) && suffix(pluralize.lowercase, 1) === 'o') return toCasing(word + 's', pluralize.casing);

        // Nouns that end in a vowel + o take the letter s:
        if (in_array(nthLast(pluralize.lowercase, 1), pluralize.vowels) && suffix(pluralize.lowercase, 1) === 'o') return toCasing(word + 's', pluralize.casing);

        // irregular suffixes that cant be pluralized
        if (suffix(pluralize.lowercase, 4) === 'ness' || suffix(pluralize.lowercase, 3) === 'ess') return word;

        // Lastly, change the word based on suffix rules
        var pluralized = autoSuffix(pluralize.lowercase);
        if (pluralized) return toCasing(sliceFromEnd(word, pluralized[0]) + pluralized[1], pluralize.casing);

        return word + 's';
    };

    function isUncountable() {
        var uncountable = [
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
        return in_array(pluralize.lowercase, uncountable);
    };

    function isIrregular() {
        var irregular = {
            'addendum': 'addenda',
            'alga': 'algae',
            'alumna': 'alumnae',
            'alumnus': 'alumni',
            'analysis': 'analyses',
            'antenna': 'antennae',
            'apparatus': 'apparatuses',
            'appendix': 'appendices',
            'axis': 'axes',
            'bacillus': 'bacilli',
            'bacterium': 'bacteria',
            'basis': 'bases',
            'beau': 'beaux',
            'kilo': 'kilos',
            'bureau': 'bureaus',
            'bus': 'busses',
            'cactus': 'cacti',
            'calf': 'calves',
            'child': 'children',
            'corps': 'corps',
            'corpus': 'corpora',
            'crisis': 'crises',
            'criterion': 'criteria',
            'curriculum': 'curricula',
            'datum': 'data',
            'deer': 'deer',
            'die': 'dice',
            'dwarf': 'dwarves',
            'diagnosis': 'diagnoses',
            'echo': 'echoes',
            'elf': 'elves',
            'ellipsis': 'ellipses',
            'embargo': 'embargoes',
            'emphasis': 'emphases',
            'erratum': 'errata',
            'fireman': 'firemen',
            'fish': 'fish',
            'focus': 'focuses',
            'foot': 'feet',
            'formula': 'formulas',
            'fungus': 'fungi',
            'genus': 'genera',
            'goose': 'geese',
            'half': 'halves',
            'hero': 'heroes',
            'hippopotamus': 'hippopotami',
            'hoof': 'hooves',
            'hypothesis': 'hypotheses',
            'index': 'indices',
            'knife': 'knives',
            'leaf': 'leaves',
            'life': 'lives',
            'loaf': 'loaves',
            'louse': 'lice',
            'man': 'men',
            'matrix': 'matrices',
            'means': 'means',
            'medium': 'media',
            'memorandum': 'memoranda',
            'millennium': 'millenniums',
            'moose': 'moose',
            'mosquito': 'mosquitoes',
            'mouse': 'mice',
            'nebula': 'nebulae',
            'neurosis': 'neuroses',
            'nucleus': 'nuclei',
            'neurosis': 'neuroses',
            'nucleus': 'nuclei',
            'oasis': 'oases',
            'octopus': 'octopi',
            'ovum': 'ova',
            'ox': 'oxen',
            'paralysis': 'paralyses',
            'parenthesis': 'parentheses',
            'person': 'people',
            'phenomenon': 'phenomena',
            'potato': 'potatoes',
            'radius': 'radii',
            'scarf': 'scarfs',
            'self': 'selves',
            'series': 'series',
            'sheep': 'sheep',
            'shelf': 'shelves',
            'scissors': 'scissors',
            'species': 'species',
            'stimulus': 'stimuli',
            'stratum': 'strata',
            'syllabus': 'syllabi',
            'symposium': 'symposia',
            'synthesis': 'syntheses',
            'synopsis': 'synopses',
            'tableau': 'tableaux',
            'that': 'those',
            'thesis': 'theses',
            'thief': 'thieves',
            'this': 'these',
            'tomato': 'tomatoes',
            'tooth': 'teeth',
            'torpedo': 'torpedoes',
            'vertebra': 'vertebrae',
            'veto': 'vetoes',
            'vita': 'vitae',
            'watch': 'watches',
            'wife': 'wives',
            'wolf': 'wolves',
            'woman': 'women',
            'is': 'are',
            'was': 'were',
            'he': 'they',
            'she': 'they',
            'i': 'we',
            'zero': 'zeroes',
        };

        if (isset(irregular[pluralize.lowercase])) return irregular[pluralize.lowercase];

        return false;
    };

    function autoSuffix() {

        var suffix1 = suffix(pluralize.lowercase, 1);
        var suffix2 = suffix(pluralize.lowercase, 2);
        var suffix3 = suffix(pluralize.lowercase, 3);

        if (suffix(pluralize.lowercase, 4) === 'zoon') return [4, 'zoa'];

        if (suffix3 === 'eau') return [3, 'eaux'];
        if (suffix3 === 'ieu') return [3, 'ieux'];
        if (suffix3 === 'ion') return [3, 'ia'];
        if (suffix3 === 'oof') return [3, 'ooves'];

        if (suffix2 === 'an') return [2, 'en'];
        if (suffix2 === 'ch') return [2, 'ches'];
        if (suffix2 === 'en') return [2, 'ina'];
        if (suffix2 === 'ex') return [2, 'exes'];
        if (suffix2 === 'is') return [2, 'ises'];
        if (suffix2 === 'ix') return [2, 'ices'];
        if (suffix2 === 'nx') return [2, 'nges'];
        if (suffix2 === 'nx') return [2, 'nges'];
        if (suffix2 === 'fe') return [2, 'ves'];
        if (suffix2 === 'on') return [2, 'a'];
        if (suffix2 === 'sh') return [2, 'shes'];
        if (suffix2 === 'um') return [2, 'a'];
        if (suffix2 === 'us') return [2, 'i'];
        if (suffix2 === 'x') return [1, 'xes'];
        if (suffix2 === 'y') return [1, 'ies'];

        if (suffix1 === 'a') return [1, 'ae'];
        if (suffix1 === 'o') return [1, 'oes'];
        if (suffix1 === 'f') return [1, 'ves'];

        return false;
    };

    function getCasing() {
        var casing = 'toLowerCase';
        casing = pluralize.lowercase === pluralize.word ? 'lower' : casing;
        casing = pluralize.upperCase === pluralize.word ? 'upper' : casing;
        casing = pluralize.sentenceCase === pluralize.word ? 'sentence' : casing;
        return casing;
    };

    function toCasing(word, casing) {
        if (casing === 'lower') return word.toLowerCase();
        if (casing === 'upper') return word.toUpperCase();
        if (casing === 'sentence') return ucfirst(word);
        return word;
    };

    function suffix(word, count) {
        return word.substr(word.length - count);
    };

    function nthLast(word, count) {
        return word.split('').reverse().join('').charAt(count);
    };

    function sliceFromEnd(word, count) {
        return word.substring(0, word.length - count);
    };

    function ucfirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    };

    function in_array(needle, haystack, argStrict) {

        var key = '',
            strict = !!argStrict;

        //we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
        //in just one for, in order to improve the performance 
        //deciding wich type of comparation will do before walk array
        if (strict) {
            for (key in haystack) {
                if (haystack[key] === needle) return true;
            }
        } else {
            for (key in haystack) {
                if (haystack[key] == needle) return true;
            }
        }

        return false;
    };

    function isset() {
        var a = arguments,
            l = a.length,
            i = 0,
            undef;

        if (l === 0) throw new Error('Empty isset');

        while (i !== l) {
            if (a[i] === undef || a[i] === null) return false;
            i++;
        }
        return true;
    };

    return pluralize;

});
