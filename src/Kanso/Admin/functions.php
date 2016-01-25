<?php
/********************************************************************************
* REQUIRED GLOBALS
*******************************************************************************/
$type = $ADMIN_PAGE_TYPE;
global $ADMIN_PAGE_TYPE;
$ADMIN_PAGE_TYPE = $type;
unset($type);

$entry = $ADMIN_WRITER_ENTRY;
global $ADMIN_WRITER_ENTRY;
$ADMIN_WRITER_ENTRY = $entry;
unset($entry);


/********************************************************************************
* HEADER FUNCTIONS
*******************************************************************************/

/**
 * Build the Admin page title
 * 
 * @return string
 */
function adminPageTitle()
{	
	$pageRequest = adminPageRequest();

	# Figure out the title based on the 
	# request type
	$title = 'Kanso';
	if ($pageRequest === 'writer') {
		$writerEntry = adminWriterEntry();
		$title = $writerEntry ? 'Edit | '.$writerEntry['title'] : 'Write | New Article';
	}              
	else if ($pageRequest === 'tags') {
		$title = 'Tags & Categories | Kanso';
	} 
	else if ($pageRequest === 'articles') {
		$title = 'Articles | Kanso';
	}
	else if ($pageRequest === 'settings') {
		$title = 'Settings | Kanso';
	}
	else if ($pageRequest === 'reset_password') {
		$title = 'Reset Your Password | Kanso';
	}
	else if ($pageRequest === 'forgot_username') {
		$title = 'Forgot Your Username | Kanso';
	}
	else if ($pageRequest === 'forgot_password') {
		$title = 'Forgot Your Password | Kanso';
	}
	else if ($pageRequest === 'login') {
		$title = 'Login | Kanso';
	}

	# Filter the title
	return \Kanso\Filters::apply('adminPageTitle', $title);
}

/**
 * Build the Admin favicons
 * 
 * @return array
 */
function adminFavicons()
{
	# Default favicons
	$favicons = [
		'<link rel="shortcut icon"                    href="'.adminAssetsUrl().'images/favicon.png">',
		'<link rel="apple-touch-icon" sizes="57x57"   href="'.adminAssetsUrl().'images/apple-touch-icon.png">',
		'<link rel="apple-touch-icon" sizes="72x72"   href="'.adminAssetsUrl().'images/apple-touch-icon-72x72.png">',
		'<link rel="apple-touch-icon" sizes="114x114" href="'.adminAssetsUrl().'images/apple-touch-icon-114x114.png">',
	];
	$favicons = \Kanso\Filters::apply('adminFavicons', $favicons);

	return implode("\n", $favicons);
}

/**
 * Build the Admin style sheets
 * 
 * @return array
 */
function adminHeaderScripts()
{
	$styles = [
		'<link rel="stylesheet" href="'.adminAssetsUrl().'css/style.css?v='.adminKansoVersion().'">'
	];
	$styles = \Kanso\Filters::apply('adminHeaderScripts', $styles);

	return implode("\n", $styles);
}

/**
 * Build the Admin body class
 * 
 * @return string
 */
function adminBodyClass()
{
	$class = '';

	if (adminIsWriter()) $class = 'writing markdown';
	
	return \Kanso\Filters::apply('adminBodyClass', $class);
}

function adminSvgSprites()
{
	$svg = [
	'<svg width="512" height="512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: none;">',
		'<g id="logo"><path fill="#62C4B6" d="M297.011,22.469c0,9.491-7.765,17.256-17.256,17.256H153.851c-9.491,0-17.255-7.765-17.255-17.256v-2.551c0-9.491,7.764-17.256,17.255-17.256h125.904c9.491,0,17.256,7.765,17.256,17.256V22.469z"/><path fill="#62C4B6" d="M404.228,88.021c0,9.491-7.766,17.256-17.256,17.256H110.73c-9.491,0-17.255-7.765-17.255-17.256v-2.55c0-9.492,7.764-17.257,17.255-17.257h276.242c9.49,0,17.256,7.765,17.256,17.257V88.021z"/><path fill="#62C4B6" d="M460.526,153.573c0,9.491-7.767,17.255-17.256,17.255H112.456c-9.492,0-17.256-7.764-17.256-17.255v-2.551c0-9.492,7.764-17.256,17.256-17.256H443.27c9.489,0,17.256,7.765,17.256,17.256V153.573z"/><path fill="#35495E" d="M404.228,211.757c0,9.489-7.766,17.256-17.256,17.256H276.566c-9.491,0-17.257-7.768-17.257-17.256v-2.554c0-9.49,7.766-17.254,17.257-17.254h110.406c9.49,0,17.256,7.764,17.256,17.254V211.757z"/><path fill="#35495E" d="M434.91,267.817c0,9.492-7.766,17.259-17.255,17.259h-78.968c-9.488,0-17.254-7.767-17.254-17.259v-2.548c0-9.49,7.766-17.255,17.254-17.255h78.968c9.489,0,17.255,7.765,17.255,17.255V267.817z"/><path fill="#35495E" d="M425.146,323.885c0,9.493-7.767,17.259-17.257,17.259H186.136c-9.491,0-17.255-7.766-17.255-17.259v-2.555c0-9.488,7.765-17.254,17.255-17.254h221.753c9.49,0,17.257,7.766,17.257,17.254V323.885z"/><path fill="#F5B54C" d="M410.804,379.95c0,9.488-7.766,17.259-17.255,17.259H112.454c-9.491,0-17.256-7.771-17.256-17.259v-2.552c0-9.488,7.766-17.255,17.256-17.255h281.095c9.489,0,17.255,7.767,17.255,17.255V379.95z"/><path fill="#FFD061" d="M381.722,436.018c0,9.488-7.767,17.255-17.256,17.255H141.538c-9.491,0-17.254-7.767-17.254-17.255v-2.556c0-9.491,7.764-17.257,17.254-17.257h222.928c9.489,0,17.256,7.766,17.256,17.257V436.018z"/><path fill="#FFD061" d="M340.821,492.08c0,9.491-7.765,17.258-17.255,17.258H182.44c-9.49,0-17.254-7.767-17.254-17.258v-2.553c0-9.488,7.764-17.254,17.254-17.254h141.125c9.49,0,17.255,7.766,17.255,17.254V492.08z"/><path fill="#62C4B6" d="M234.892,219.125c0,9.491-7.766,17.257-17.257,17.257H115.601c-9.491,0-17.255-7.766-17.255-17.257v-2.551c0-9.49,7.764-17.256,17.255-17.256h102.034c9.491,0,17.257,7.766,17.257,17.256V219.125z"/></g>',
		'<g id="search"><path d="M60.392,52.844c3.96-5.483,6.296-12.219,6.296-19.5C66.688,14.929,51.759,0,33.344,0C14.929,0,0,14.929,0,33.344c0,18.414,14.929,33.344,33.344,33.344c7.281,0,14.015-2.336,19.499-6.296L84.875,97L97,84.875L60.392,52.844L60.392,52.844z M33.344,57.596c-13.392,0-24.25-10.857-24.25-24.25s10.856-24.25,24.25-24.25c13.392,0,24.25,10.856,24.25,24.25S46.737,57.596,33.344,57.596z"/></g>',
		'<g id="error"><path d="M95.994,12.282L84.719,1.006L48.5,37.225L12.282,1.006L1.006,12.282L37.225,48.5L1.006,84.719l11.275,11.275L48.5,59.775 l36.219,36.219l11.275-11.275L59.775,48.5L95.994,12.282z"/></g>',
		'<g id="success"><path d="M87.108,11.5L32.413,66.195L9.892,43.674l-9.652,9.652L32.413,85.5l64.348-64.348L87.108,11.5z"/></g>',
		'<g id="alert"><path d="M48.467,96.688c-1.657,0-3.25-0.318-4.735-0.943c-1.476-0.621-2.78-1.492-3.875-2.586c-1.095-1.096-1.966-2.398-2.589-3.877c-0.625-1.488-0.942-3.102-0.942-4.801s0.317-3.314,0.942-4.797c0.622-1.477,1.493-2.781,2.589-3.879c1.097-1.096,2.4-1.965,3.876-2.588c1.485-0.625,3.078-0.941,4.735-0.941c1.658,0,3.251,0.316,4.735,0.941c1.479,0.623,2.782,1.494,3.877,2.59c1.089,1.088,1.966,2.383,2.607,3.848c0.655,1.494,0.988,3.119,0.988,4.826s-0.333,3.332-0.988,4.828c-0.644,1.467-1.521,2.762-2.61,3.85c-1.092,1.092-2.395,1.963-3.873,2.586C51.719,96.369,50.125,96.688,48.467,96.688z M39.67,69.223l-2.532-68.91h22.723l-2.532,68.91H39.67z"/></g>',
		'<g id="info"><path d="M48.5,0.375C21.922,0.375,0.375,21.922,0.375,48.5S21.922,96.625,48.5,96.625S96.625,75.078,96.625,48.5S75.078,0.375,48.5,0.375z M42.484,22.934c0-2.481,2.03-4.512,4.512-4.512h3.008c2.482,0,4.512,2.03,4.512,4.512v3.008c0,2.481-2.029,4.512-4.512,4.512h-3.008c-2.481,0-4.512-2.03-4.512-4.512V22.934z M60.531,78.578H36.469v-6.016h6.016V48.5h-6.016v-6.016h18.047v30.078h6.016V78.578z"/></g>',
		'<g id="arrow-down"><path d="M96.307,29.078l-8.963-8.964L48.5,58.957L9.657,20.114l-8.964,8.964L48.5,76.886L96.307,29.078z"/></g>',
		'<g id="arrow-up"><path d="M0.781,67.887l8.947,8.946L48.5,38.062l38.771,38.771l8.947-8.946L48.5,20.167L0.781,67.887z"/></g>',
		'<g id="arrow-right"><path d="M29,0.5l-9,9l39,39l-39,39l9,9l48-48L29,0.5z"/></g>',
		'<g id="arrow-left"><path d="M68.135,96.834l9.063-9.062L37.927,48.5L77.198,9.229l-9.063-9.062L19.802,48.5L68.135,96.834z"/></g>',
		'<g id="trash"><path d="M15.5,30v60c0,3.301,2.7,6,6,6h54c3.301,0,6-2.699,6-6V30H15.5z M33.5,84h-6V42h6V84z M45.5,84h-6V42h6V84z M57.5,84h-6V42h6V84z M69.5,84h-6V42h6V84z"/><path d="M83,12H63.5V4.5C63.5,2.024,61.476,0,59,0H38c-2.476,0-4.5,2.024-4.5,4.5V12H14c-2.476,0-4.5,2.024-4.5,4.5V24h78v-7.5C87.5,14.024,85.476,12,83,12z M57.5,12h-18V6.075h18V12z"/></g>',
		'<g id="pen"><path d="M50.399,23.119c-4.018,3.897-12.143,11.962-13.571,13.391C25.686,47.651-0.475,78.688,0.006,96.994c18.309,0.484,49.344-25.68,60.488-36.82c1.428-1.431,9.492-9.556,13.389-13.573L50.399,23.119z M89.487,19.342l-1.709-1.707L97,10.015L86.984,0l-7.619,9.224l-1.71-1.712c-5.284-0.31-13.808,4.807-22.208,11.564L77.925,41.55c2.542-3.162,4.834-6.334,6.711-9.35c5.642,4.648,1.474,18.292-22.325,36.176l4.377,4.374c21.219-15.156,34.957-33.344,21.646-47.786C89.129,22.866,89.579,20.942,89.487,19.342z"/></g>',
		'<g id="hamburger"><path d="M0.9,14.5h95.2v13.6H0.9V14.5z"/><path d="M0.9,41.7h95.2v13.6H0.9V41.7z"/><path d="M0.9,68.9h95.2v13.6H0.9V68.9z"/></g>',
		'<g id="image"><path d="M81.779,36.398L60.602,57.576L42.449,39.424L12.195,69.678v9.076h72.609V36.398H81.779z"/><path d="M87.832,12.195c1.639,0,3.023,1.386,3.023,3.025v66.559c0,1.641-1.385,3.025-3.023,3.025H9.169c-1.64,0-3.025-1.385-3.025-3.025V15.22c0-1.64,1.386-3.025,3.025-3.025H87.832z M87.832,6.144H9.169c-4.992,0-9.076,4.084-9.076,9.076v66.559c0,4.992,4.084,9.076,9.076,9.076h78.663c4.99,0,9.074-4.084,9.074-9.076V15.22C96.906,10.229,92.822,6.144,87.832,6.144L87.832,6.144z"/></g>',
		'<g id="strike-through"><path d="M478.91,256v31.844h-90.517c16.939,22.764,26.829,50.145,26.829,79.609c0,79.144-71.282,143.297-159.219,143.297c-81.949,0-149.438-55.727-158.238-127.375h64.675c3.491,14.088,11.553,27.234,23.579,38.071c18.363,16.521,43.211,25.616,69.978,25.616s51.614-9.095,69.978-25.616c16.481-14.834,25.554-34.005,25.554-53.993s-9.072-39.159-25.554-53.993c-18.363-16.521-43.211-25.616-69.978-25.616H33.09V256h445.812H478.91z M256.003,224.156h-132.39c-16.94-22.768-26.829-50.145-26.829-79.609c0-79.14,71.283-143.297,159.219-143.297c81.951,0,149.439,55.727,158.231,127.375H349.56c-3.482-14.084-11.553-27.234-23.578-38.072c-18.355-16.515-43.219-25.623-69.978-25.623s-51.622,9.099-69.977,25.623c-16.474,14.835-25.554,34.005-25.554,53.994s9.08,39.159,25.554,53.994C204.381,215.056,229.244,224.156,256.003,224.156z"/></g>',
		'<g id="bold"><path d="M367.664,240.412c21.377-25.395,34.275-58.147,34.275-93.866c0-80.47-65.47-145.939-145.939-145.939H73.576v510.787h218.909c80.469,0,145.938-65.47,145.938-145.939C438.424,312.455,410.045,265.977,367.664,240.412z M183.031,73.576h57.863c31.905,0,57.862,32.735,57.862,72.969c0,40.235-25.957,72.97-57.862,72.97h-57.863V73.576z M273.673,438.424h-90.642V292.484h90.642c33.322,0,60.428,32.735,60.428,72.97C334.101,405.689,306.995,438.424,273.673,438.424z"/></g>',
		'<g id="italic"><path d="M448.188-0.25v32.031h-65.345L214.678,480.219h73.353v32.031H63.812v-32.031h65.345L297.322,31.781h-73.354V-0.25H448.188z"/></g>',
		'<g id="picture"><path d="M432.91,191.669L320.33,304.248l-96.496-96.496L63.007,368.579v48.248h385.985V191.669H432.91z"/><path d="M465.076,63.007c8.715,0,16.082,7.366,16.082,16.083v353.82c0,8.717-7.367,16.082-16.082,16.082H46.924c-8.717,0-16.083-7.365-16.083-16.082V79.09c0-8.717,7.366-16.083,16.083-16.083H465.076z M465.076,30.842H46.924c-26.535,0-48.248,21.713-48.248,48.248v353.82c0,26.535,21.713,48.248,48.248,48.248h418.152c26.533,0,48.248-21.713,48.248-48.248V79.09C513.324,52.555,491.609,30.842,465.076,30.842L465.076,30.842z"/></g>',
		'<g id="link"><path d="M159.901,352.094c8.806,8.806,23.664,8.367,33.001-0.97l158.21-158.209c9.345-9.345,9.782-24.191,0.97-33.001c-8.813-8.81-23.664-8.367-33.001,0.97l-158.21,158.209C151.526,328.438,151.096,343.288,159.901,352.094z M238.416,337.627c2.284,4.535,3.503,9.587,3.503,14.865c0,8.813-3.378,17.057-9.509,23.172l-81.767,81.774c-6.124,6.131-14.358,9.502-23.171,9.502c-8.813,0-17.056-3.379-23.171-9.502l-49.736-49.743c-6.139-6.131-9.509-14.358-9.509-23.172c0-8.812,3.37-17.048,9.509-23.171l81.767-81.767c6.115-6.131,14.358-9.51,23.171-9.51c5.279,0,10.331,1.22,14.866,3.504l32.704-32.708c-13.951-10.714-30.756-16.094-47.57-16.094c-20.012,0-40.023,7.589-55.202,22.772l-81.767,81.771c-30.374,30.366-30.374,80.047,0,110.421l49.736,49.735c15.179,15.179,35.19,22.772,55.202,22.772s40.023-7.594,55.203-22.772l81.768-81.767c27.941-27.941,30.154-72.227,6.679-102.772l-32.705,32.704V337.627z M489.474,72.262l-49.736-49.74C424.551,7.343,404.539-0.25,384.527-0.25s-40.023,7.593-55.202,22.772l-81.776,81.771c-27.933,27.937-30.139,72.223-6.678,102.772l32.704-32.704c-2.275-4.532-3.495-9.595-3.495-14.862c0-8.817,3.386-17.052,9.501-23.175l81.775-81.771c6.131-6.131,14.357-9.502,23.171-9.502c8.821,0,17.048,3.379,23.179,9.502l49.736,49.74c6.131,6.123,9.501,14.358,9.501,23.175c0,8.813-3.378,17.048-9.501,23.171l-81.768,81.771c-6.131,6.123-14.357,9.505-23.179,9.505c-5.271,0-10.322-1.22-14.858-3.5l-32.703,32.708c13.95,10.714,30.748,16.094,47.562,16.094c20.012,0,40.023-7.593,55.21-22.772l81.768-81.774C519.839,152.305,519.839,102.628,489.474,72.262z"/></g>',
		'<g id="keyboard"><path d="M482.629,86.028H29.371c-15.582,0-28.329,12.747-28.329,28.329v283.288c0,15.582,12.747,28.328,28.329,28.328h453.258c15.584,0,28.329-12.746,28.329-28.328V114.357C510.958,98.775,498.213,86.028,482.629,86.028z M284.329,142.686h56.657v56.658h-56.657V142.686z M369.315,227.672v56.656h-56.657v-56.656H369.315z M199.342,142.686H256v56.658h-56.658V142.686z M284.329,227.672v56.656h-56.658v-56.656H284.329z M114.357,142.686h56.656v56.658h-56.656V142.686z M199.342,227.672v56.656h-56.656v-56.656H199.342z M57.7,142.686h28.329v56.658H57.7V142.686z M57.7,227.672h56.658v56.656H57.7V227.672z M86.029,369.314H57.7v-56.658h28.329V369.314z M340.986,369.314H114.357v-56.658h226.629V369.314z M454.3,369.314h-84.984v-56.658H454.3V369.314z M454.3,284.328h-56.655v-56.656H454.3V284.328z M454.3,199.343h-84.984v-56.658H454.3V199.343z"/> </g>',
		'<g id="disk"><path d="M432.496,0H16.019C7.208,0,0,7.208,0,16.019v480.551c0,8.807,7.208,16.019,16.019,16.019h480.551c8.807,0,16.019-7.212,16.019-16.019V80.092L432.496,0z M128.147,16.019H384.44v128.146c0,8.807-7.211,16.019-16.018,16.019H144.165c-8.811,0-16.018-7.211-16.018-16.019V16.019z M448.515,496.569H64.073V288.33c0-17.691,14.345-32.036,32.037-32.036h320.367c17.692,0,32.037,14.345,32.037,32.036V496.569z"/><path d="M288.33,32.037h64.074v96.11H288.33V32.037z"/><path d="M128.147,288.33H384.44v32.037H128.147V288.33z"/><path d="M128.147,352.404H384.44v32.036H128.147V352.404z"/><path d="M128.147,416.478H384.44v32.036H128.147V416.478z"/></g>',
		'<g id="book-open"><path d="M508.438,108.642c-25.562-31.663-73.232-51.327-124.449-51.327s-98.903,19.664-124.445,51.327c-2.293,2.844-3.551,6.39-3.551,10.046c0-3.656-1.258-7.203-3.551-10.046c-25.546-31.663-73.232-51.327-124.445-51.327S29.097,76.979,3.551,108.642C1.258,111.485,0,115.032,0,118.688v319.991c0,6.781,4.281,12.828,10.671,15.094c6.391,2.258,13.519,0.234,17.785-5.047c19.621-24.312,57.764-39.421,99.548-39.421s79.927,15.109,99.548,39.421c3.098,3.844,7.714,5.953,12.457,5.953H272c4.741,0,9.358-2.109,12.46-5.953c19.624-24.312,57.764-39.421,99.544-39.421s79.928,15.109,99.544,39.421c4.266,5.281,11.391,7.297,17.78,5.047c6.391-2.266,10.672-8.312,10.672-15.094V118.688c0-3.656-1.25-7.203-3.547-10.046H508.438z M223.993,403.594c-26.007-16.647-60.135-26.288-95.997-26.288s-69.99,9.641-95.997,26.288V124.649c20.449-21.913,56.607-35.343,95.997-35.343s75.548,13.422,95.997,35.343V403.594z M479.985,403.594c-26.015-16.647-60.139-26.288-95.997-26.288s-69.982,9.641-95.997,26.288V124.649c20.452-21.913,56.607-35.343,95.997-35.343s75.545,13.422,95.997,35.343V403.594z"/><path d="M319.99,153.312h127.996v31.999H319.99V153.312z"/><path d="M319.99,217.31h127.996v31.999H319.99V217.31z"/><path d="M319.99,281.309h95.997v31.999H319.99V281.309z"/><path d="M63.998,153.312h127.996v31.999H63.998V153.312z"/><path d="M63.998,217.31h127.996v31.999H63.998V217.31z"/><path d="M63.998,281.309h95.997v31.999H63.998V281.309z"/></g>',
		'<g id="file-text"><path d="M458.906,114.535c-11.109-15.145-26.586-32.848-43.594-49.848s-34.703-32.48-49.844-43.59C339.68,2.184,327.172,0,320,0H72C49.945,0,32,17.945,32,40v432c0,22.055,17.945,40,40,40h368c22.055,0,40-17.945,40-40V160C480,152.832,477.812,140.328,458.906,114.535z M392.688,87.312c15.352,15.352,27.406,29.199,36.289,40.688H352V51.023C363.5,59.914,377.336,71.961,392.688,87.312z M448,472c0,4.336-3.664,8-8,8H72c-4.336,0-8-3.664-8-8V40c0-4.336,3.664-8,8-8c0,0,247.977,0,248,0v112c0,8.84,7.156,16,16,16h112V472z"/><path d="M368,416H144c-8.84,0-16-7.156-16-16s7.16-16,16-16h224c8.844,0,16,7.156,16,16S376.844,416,368,416z"/><path d="M368,352H144c-8.84,0-16-7.156-16-16s7.16-16,16-16h224c8.844,0,16,7.156,16,16S376.844,352,368,352z"/><path d="M368,288H144c-8.84,0-16-7.156-16-16s7.16-16,16-16h224c8.844,0,16,7.156,16,16S376.844,288,368,288z"/></g>',
		'<g id="checkmark"><path d="M487.955,35.411C323.244,160.073,165.279,326.806,165.279,326.806L51.445,224.487L3.89,272.932 c46.79,43.964,155.192,160.126,191.855,203.665C298.948,304.17,406.23,175.568,508.11,57.976l-20.155-22.573V35.411z"/></g>',
		'<g id="cross"><path d="M95.994,12.282L84.719,1.006L48.5,37.225L12.282,1.006L1.006,12.282L37.225,48.5L1.006,84.719l11.275,11.275L48.5,59.775 l36.219,36.219l11.275-11.275L59.775,48.5L95.994,12.282z"/></g>',
		'<g id="blocked"><path d="M438.026,73.98C389.406,25.361,324.762-1.419,256-1.419c-68.761,0-133.4,26.78-182.026,75.399 C25.353,122.601-1.428,187.247-1.428,255.999c0,68.756,26.78,133.4,75.401,182.021c48.619,48.618,113.265,75.399,182.026,75.399 c68.762,0,133.4-26.781,182.027-75.399c48.62-48.62,75.401-113.265,75.401-182.027c0-68.762-26.781-133.398-75.401-182.026V73.98z M449.071,255.999c0,41.637-13.244,80.241-35.743,111.805L144.204,98.695c31.579-22.503,70.175-35.752,111.811-35.752 c106.455,0,193.065,86.61,193.065,193.065L449.071,255.999z M62.943,255.999c0-41.636,13.244-80.238,35.743-111.803l269.124,269.108 c-31.579,22.5-70.175,35.752-111.811,35.752c-106.454,0-193.065-86.609-193.065-193.064L62.943,255.999z"/></g>',
		'<g id="user"><path d="M24.537,24.125c0-13.118,10.633-23.75,23.75-23.75s23.749,10.632,23.749,23.75c0,13.118-10.632,23.75-23.749,23.75 S24.537,37.243,24.537,24.125z M72.036,53.813H24.537c-13.116,0-23.75,10.633-23.75,23.749V83.5h95v-5.938 C95.787,64.446,85.153,53.813,72.036,53.813z"/></g>',
		'<g id="screen"><path d="M0.522,32.521v319.996H512.52V32.521H0.522z M480.52,320.518H32.521V64.521H480.52V320.518z M336.52,384.518H176.518l-16,64 l-31.998,32h256l-32-32L336.52,384.518z"/></g>',
		'<g id="wink"><path d="M255.521,2.923C116.024,2.923,2.922,116.024,2.922,255.521c0,139.513,113.102,252.6,252.599,252.6 c139.513,0,252.6-113.087,252.6-252.6C508.121,116.024,395.034,2.923,255.521,2.923z M350.246,129.222 c17.438,0,31.575,21.207,31.575,47.363c0,26.163-14.138,47.361-31.575,47.361c-17.437,0-31.575-21.198-31.575-47.361 C318.671,150.429,332.81,129.222,350.246,129.222z M176.584,156.881c29.433,0,50.323,11.023,50.323,28.261 c0,3.653,1.881,21.214-0.085,24.475c-7.315-12.126-27.042-20.821-50.238-20.821c-23.188,0-42.922,8.695-50.229,20.821 c-1.966-3.261-0.093-20.821-0.093-24.475C126.262,167.904,147.16,156.881,176.584,156.881z M249.771,413.396 c-58.833,0-110.442-31.714-139.573-79.4c34.75,27.166,95.973,32.47,160.495,19.719c69.193-13.659,122.893-45.157,142.303-87.124 c-10.685,82.869-79.692,146.82-163.225,146.82V413.396z"/></g>',
		'<g id="evil"><path d="M496.555,59.684c0-21.42-4.487-41.796-12.549-60.259c-15.814,36.176-45.4,64.937-82.149,79.648c-40.575-31.041-91.3-49.52-146.333-49.52c-55.035,0-105.761,18.464-146.336,49.52C72.445,64.361,42.854,35.601,27.038-0.575c-8.062,18.463-12.549,38.839-12.549,60.259c0,34.639,11.703,66.541,31.351,91.977c-19.941,35.102-31.351,75.677-31.351,118.925c0,133.125,107.925,241.034,241.034,241.034c133.122,0,241.031-107.909,241.031-241.034c0-43.248-11.401-83.823-31.351-118.925C484.859,126.225,496.555,94.322,496.555,59.684z M285.693,208.697c0.957-22.347,21.288-37.169,38.412-45.738c16.345-8.18,32.527-12.27,33.204-12.432c8.077-2.016,16.257,2.891,18.271,10.96c2.016,8.062-2.897,16.242-10.96,18.265c-8.297,2.097-18.139,5.716-26.805,10.048c5.018,5.378,8.077,12.594,8.077,20.53c0,16.639-13.49,30.129-30.13,30.129c-16.639,0-30.129-13.49-30.129-30.129c0-0.559,0.015-1.104,0.044-1.647L285.693,208.697z M135.463,161.487c2.016-8.076,10.195-12.976,18.265-10.96c0.684,0.177,16.859,4.267,33.211,12.432c17.124,8.569,37.456,23.392,38.412,45.738c0.029,0.537,0.044,1.089,0.044,1.633c0,16.639-13.49,30.129-30.129,30.129s-30.13-13.49-30.13-30.129c0-7.93,3.075-15.139,8.077-20.522c-8.665-4.34-18.508-7.959-26.797-10.048c-8.07-2.016-12.983-10.195-10.961-18.265L135.463,161.487z M255.523,421.231c-54.844,0-102.841-29.32-129.189-73.131l38.758-23.244c18.441,30.658,52.042,51.182,90.432,51.182c38.395,0,71.995-20.523,90.429-51.182l38.751,23.244C358.354,391.911,310.365,421.231,255.523,421.231L255.523,421.231z"/></g>',
		'<g id="nuetral"><path d="M255.521,1.422C115.196,1.422,1.422,115.196,1.422,255.521c0,140.341,113.774,254.1,254.099,254.1c140.341,0,254.1-113.759,254.1-254.1C509.621,115.196,395.862,1.422,255.521,1.422z M319.047,382.571h-127.05v-31.763h127.05V382.571z M350.809,128.472c17.541,0,31.763,14.222,31.763,31.763s-14.222,31.763-31.763,31.763s-31.762-14.222-31.762-31.763S333.268,128.472,350.809,128.472z M160.234,128.472c17.541,0,31.763,14.222,31.763,31.763s-14.222,31.763-31.763,31.763s-31.763-14.222-31.763-31.763S142.693,128.472,160.234,128.472z"/></g>',
	'</svg>',
	];

	$svg = \Kanso\Filters::apply('adminSvgSprites', $svg);

	return implode("\n", $svg);
}

function adminHeaderLinks()
{
	$links = [
		'<li class="logo"><a href="/admin/settings/account"><svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg></a></li>',
		'<li><a href="http://kanso-cms.github.io/">Kanso</a></li>',
		'<li><a href="http://kanso-cms.github.io/docs/0.0.01/">Documentation</a></li>',
		'<li><a href="https://github.com/kanso-cms/cms">GitHub</a></li>',
	];

	$links = \Kanso\Filters::apply('adminHeaderLinks', $links);

	return implode("\n", $links);
}

function adminHeaderAuthorImg()
{
	$user = adminGetUser();
	$env  = \Kanso\Kanso::getInstance()->Environment;
	if (trim($user['thumbnail']) === '') {
		return '';
	}
	else {
		return '<img class="author-img" src="'.$env['KANSO_IMGS_URL'].$user['thumbnail'].'"  height="24" width="24" />';
	}
}

function adminHeaderName()
{
	$user = adminGetUser();
	if (str_word_count($user['name']) > 1) {
		$words = explode(' ', trim($user['name']));
		return strtoupper($words[0][0]).'. '.$words[1];
	}
	else {
		return $user['name'];
	}
}

function adminHeaderDropdown()
{
	$links = [
		'<li><a href="/admin/settings/account">Settings</a></li>',
		'<li><a href="/admin/articles/">Articles</a></li>',
		'<li><a href="/admin/write/">Write</a></li>',
		'<li><a href="/admin/logout/">Logout</a></li>',
	];

	$links = \Kanso\Filters::apply('adminHeaderDropdown', $links);

	return implode("\n", $links);
}



/********************************************************************************
* TABS NAV FOR ARTICLES AND SETTINGS PAGES
*******************************************************************************/
function adminTabNav()
{
	$tabs = [];

	$activeTab = adminActiveTabName();

	if (adminIsArticles() || adminIsTaxonomy() || adminIsComments()) {
		$tabs = [
			[ 
				'url'      => 'articles',
				'title'    => 'Articles',
				'targetEl' => 'posts-panel',
				'active'   => $activeTab === 'articles',
				'text'     => 'Articles',
				'class'    => '',
			],
			[ 
				'url'      => 'taxonomy',
				'title'    => 'Taxonomy',
				'targetEl' => 'tags-panel',
				'active'   => $activeTab === 'taxonomy',
				'text'     => 'Tags &amp; Categories',
				'class'    => '',
			],
			[ 
				'url'      => 'comments',
				'title'    => 'Comments',
				'targetEl' => 'comments-panel',
				'active'   => $activeTab === 'comments',
				'text'     => 'Comments',
				'class'    => '',
			],
		];
	}
	else if (adminIsSettings()) {
		$user = adminGetUser();
		$tabs = [
			[ 
				'url'      => 'account',
				'title'    => 'Account',
				'targetEl' => 'account-panel',
				'active'   => $activeTab === 'account',
				'text'     => 'Account',
				'class'    => '',
			],
			[ 
				'url'      => 'author',
				'title'    => 'Author',
				'targetEl' => 'author-panel',
				'active'   =>  $activeTab === 'author',
				'text'     => 'Author',
				'class'    => '',
			],
		];
		if ($user['role'] === 'administrator') {
			$tabs[] = [ 
				'url'      => 'kanso',
				'title'    => 'Kanso',
				'targetEl' => 'kanso-panel',
				'active'   => $activeTab === 'kanso',
				'text'     => 'Kanso',
				'class'    => '',
			];
			$tabs[] = [ 
				'url'      => 'users',
				'title'    => 'Users',
				'targetEl' => 'users-panel',
				'active'   =>  $activeTab === 'users',
				'text'     => 'Users',
				'class'    => '',
			];
			$tabs[] = [ 
				'url'      => 'tools',
				'title'    => 'Tools',
				'targetEl' => 'tools-panel',
				'active'   =>  $activeTab === 'tools',
				'text'     => 'Tools',
				'class'    => '',
			];
		}
	}

	$tabs   = \Kanso\Filters::apply('adminTabNav', $tabs);
	
	$tabStr = '';

	foreach ($tabs as $link) {
		$active = $link['active'] === true ? 'active' : '';
		$class  = $link['class'] === '' ? $active : $link['class'].' '.$link['active'];
		$tabStr .= '<li><a data-tab-url="'.$link['url'].'" data-tab-title="'.$link['title'].'" data-tab="'.$link['targetEl'].'" href="#" class="'.$class.'">'.$link['text'].'</a></li>';
	}

	return $tabStr;
}

/********************************************************************************
* TABS PANELS FOR ARTICLES AND SETTINGS PAGES
*******************************************************************************/

function adminTabPanels()
{
	$panels = [];

	if (adminIsArticles() || adminIsTaxonomy() || adminIsComments()) {
		$panels = [
			[ 
				'active'    =>  adminActiveTabName() === 'articles',
				'class'     => '',
				'id'        => 'posts-panel',
				'file_path' => adminIncludesDir().'Admin/Sections/articles.php',
			],
			[ 
				'active'    =>  adminActiveTabName() === 'taxonomy',
				'class'     => '',
				'id'        => 'tags-panel',
				'file_path' => adminIncludesDir().'Admin/Sections/tags.php',
			],
			[ 
				'active'    =>  adminActiveTabName() === 'comments',
				'class'     => '',
				'id'        => 'comments-panel',
				'file_path' => adminIncludesDir().'Admin/Sections/comments.php',
			],
		];
	}
	else if (adminIsSettings()) {
		$user   = adminGetUser();
		$panels = [
			[ 
				'active'    =>  adminActiveTabName() === 'account',
				'class'     => 'row form-section tab-panel',
				'id'        => 'account-panel',
				'file_path' => adminIncludesDir().'Admin/Sections/settingsAccount.php',
			],
			[ 
				'active'    =>  adminActiveTabName() === 'author',
				'class'     => 'row form-section tab-panel',
				'id'        => 'author-panel',
				'file_path' => adminIncludesDir().'Admin/Sections/settingsAuthor.php',
			],
		];
		if ($user['role'] === 'administrator') {
			$panels[] = [ 
				'active'    =>  adminActiveTabName() === 'kanso',
				'class'     => 'row form-section tab-panel',
				'id'        => 'kanso-panel',
				'file_path' => adminIncludesDir().'Admin/Sections/settingsKanso.php',
			];
			$panels[] = [ 
				'active'    =>  adminActiveTabName() === 'users',
				'class'     => 'row form-section tab-panel',
				'id'        => 'users-panel',
				'file_path' => adminIncludesDir().'Admin/Sections/settingsUsers.php',
			];
			$panels[] = [ 
				'active'    =>  adminActiveTabName() === 'tools',
				'class'     => 'row form-section tab-panel',
				'id'        => 'tools-panel',
				'file_path' => adminIncludesDir().'Admin/Sections/settingsTools.php',
			];
		}
	}

	$panels = \Kanso\Filters::apply('adminTabPanels', $panels);
	return $panels;

}

/********************************************************************************
* ADMIN ARTICLES PAGE
*******************************************************************************/



/********************************************************************************
* ADMIN SETTINGS PAGE
*******************************************************************************/
function adminSettingsUserImg()
{
	$user = adminGetUser();
	$env  = \Kanso\Kanso::getInstance()->Environment;
	if (trim($user['thumbnail']) === '') {
		return '';
	}
	else {
		return '<img src="'.$env['KANSO_IMGS_URL'].$user['thumbnail'].'" />';
	}
}

function adminSettingsThumbnailSizes()
{
	$thumnails    = adminKansoConfig('KANSO_THUMBNAILS');
	$thumnailList = '';
	foreach ($thumnails as $i => $size) {
		if (is_array($size)) {
			$thumnailList .= $size[0].' '.$size[1].', ';
		}
		else {
			$thumnailList .= $size.', ';
		}
	}
	return rtrim($thumnailList, ', ');
}


/**
 * Build theme radio buttons 
 *
 * This function builds an HTML valid list of radio buttons
 * for choosing the active theme for Kanso.
 *
 * @return string 
 */
function adminThemeRadios() 
{
	$radios = '';
	$env    = \Kanso\Kanso::getInstance()->Environment;
	$config = \Kanso\Kanso::getInstance()->Config;
	$themes = array_filter(glob($env['KANSO_THEME_DIR'].'/*'), 'is_dir');
	if ($themes) {
		foreach ($themes as $i => $themeName) {
			$themeName = substr($themeName, strrpos($themeName, '/') + 1);
			$checked     = ($themeName === $config['KANSO_THEME_NAME'] ? 'checked' : '');
			$radios .= '
			<div class="radio-wrap">
				<input id="themRadio'.$i.'" class="js-kanso-theme" type="radio" name="theme" '.$checked.' value="'.$themeName.'">
				<label class="radio small" for="themRadio'.$i.'"></label>
				<p class="label">'.$themeName.'</p>
			</div>
			';
		}
	}
	return $radios;
}

function adminSettingsStaticPages()
{
	return implode(', ', adminKansoConfig('KANSO_STATIC_PAGES'));
}


/********************************************************************************
* ADMIN WRITER PAGE
*******************************************************************************/
function adminWriterEntry()
{
	global $ADMIN_WRITER_ENTRY;
    return $ADMIN_WRITER_ENTRY;
}

function adminHeroDZActive()
{
	$writerEntry = adminWriterEntry();
	if (isset($writerEntry['thumbnail']) && $writerEntry['thumbnail'] !== '') {
		return 'dz-started';
	}
	return '';
}

function adminWriterContent()
{
	$entry = adminWriterEntry();
	if ($entry) return urldecode($entry['content']);
	return '';
}

function adminWriterAjaxType()
{
	$entry = adminWriterEntry();
	if ($entry) return 'writer_save_existing_article';
	return 'writer_save_new_article';
}

function adminWriterPostId()
{
	$entry = adminWriterEntry();
	if ($entry) return (int) $entry['id'];
	return null;
}
function adminWriterTheTitle()
{
	$entry = adminWriterEntry();
	if ($entry) return $entry['title'];
	return '';
}

function adminWriterTheCategory()
{
	$entry = adminWriterEntry();
	if ($entry) return $entry['category'];
	return '';
}
function adminWriterTheTags()
{
	$entry = adminWriterEntry();
	if ($entry) return $entry['tags'];
	return '';
}
function adminWriterTheExcerpt()
{
	$entry = adminWriterEntry();
	if ($entry) return urldecode($entry['excerpt']);
	return '';
}
function adminWriterHeroImg()
{
	$heroSrc = adminWriterHeroSrc();
	
	if (empty($heroSrc)) return '';
	return '<div class="dz-preview dz-image-preview dz-processing dz-success"> <div class="dz-details"><img src="'.$heroSrc.'"/></div></div>';
}
function adminWriterHeroSrc()
{
	$env    = \Kanso\Kanso::getInstance()->Environment;
	$entry  = adminWriterEntry();
	if (trim($entry['thumbnail']) === '') return '';
	return $env['KANSO_IMGS_URL'].str_replace('_large', '_medium', $entry['thumbnail']);
}
function adminWriterEnabledComments()
{
	$entry  = adminWriterEntry();
	return intval($entry['comments_enabled']) > 0 ? 'checked' : '';
}
function adminWriterPostTypeSelect()
{
	$select       = '<select class="input-default small" name="type">';
	$postTypes    = adminPostTypes();
	$entry        = adminWriterEntry();
	$hasSelection = !empty($entry);
	
	foreach ($postTypes as $postType) {
		
		if (!$hasSelection && $postType === 'post') {
			$selected = 'selected';
		}
		else {
			$selected = '';
		}
		
		if ($entry && $entry['type'] === $postType) $selected = 'selected';
		$select .= '<option value="'.$postType.'" '.$selected.'>'.$postType.'</option>';
	}

	return $select.'</select>';
}

/********************************************************************************
* FOOTER FUNCTIONS
*******************************************************************************/

function adminFooterScripts()
{
	$scripts = [];

	# Ajax
	$scripts[] = '<script type="text/javascript" src="'.adminAssetsUrl().'js/vendor/simpleAjax.js?v='.adminKansoVersion().'"></script>';
	
	# Input masker
	$scripts[] = '<script type="text/javascript" src="'.adminAssetsUrl().'js/vendor/vanillaMasker.js?v='.adminKansoVersion().'"></script>';

	# Dropzone
	$scripts[] = '<script type="text/javascript" src="'.adminAssetsUrl().'js/vendor/dropzone.js?v='.adminKansoVersion().'"></script>';

	# Admin scripts
	$scripts[] = '<script type="text/javascript" src="'.adminAssetsUrl().'js/scripts.js?v='.adminKansoVersion().'"></script>';

	# Write Js
	if (adminIsWriter()) {
		# Codemirror
		$scripts[] = '<script type="text/javascript" src="'.adminAssetsUrl().'js/vendor/codemirror.js?v='.adminKansoVersion().'"></script>';
		# Highlight js
		$scripts[] = '<script type="text/javascript" src="'.adminAssetsUrl().'js/vendor/highlight.js?v='.adminKansoVersion().'"></script>';
		# Markdownit
		$scripts[] = '<script type="text/javascript" src="'.adminAssetsUrl().'js/vendor/markdownIt.js?v='.adminKansoVersion().'"></script>';
	 	# Write application
	 	$scripts[] = '<script type="text/javascript" src="'.adminAssetsUrl().'js/writer.js?v='.adminKansoVersion().'"></script>';
	}

	return \Kanso\Filters::apply('adminFooterScripts', $scripts);
}


/********************************************************************************
* HELPER FUNCTIONS
*******************************************************************************/
function adminAssetsUrl()
{
	$env = \Kanso\Kanso::getInstance()->Environment;
	return str_replace($env['DOCUMENT_ROOT'], $env['HTTP_HOST'], $env['KANSO_ADMIN_DIR']).'/assets/';
}
function adminIncludesDir()
{
	$env = \Kanso\Kanso::getInstance()->Environment;
	return $env['KANSO_ADMIN_DIR'].DIRECTORY_SEPARATOR.'Views'.DIRECTORY_SEPARATOR;
}
function adminKansoConfig($key = null)
{
	$config = \Kanso\Kanso::getInstance()->Config;
	if ($key) {
		if (array_key_exists($key, $config)) return $config[$key];
		return false;
	}
	return $config;
}
function adminKansoVersion()
{
	return \Kanso\Kanso::getInstance()->Version;
}

function adminGetUser($key = null)
{
	if ($key) {
		$user = \Kanso\Kanso::getInstance()->Session->get('KANSO_ADMIN_DATA');
		if (isset($user[$key])) return $user[$key];
		return false;
	}
	return \Kanso\Kanso::getInstance()->Session->get('KANSO_ADMIN_DATA');
}

function adminPageRequest()
{
	global $ADMIN_PAGE_TYPE;
    return $ADMIN_PAGE_TYPE;
}
function adminIsDashboard()
{
	$accountPages = ['login', 'resetpassword', 'register', 'forgotpassword', 'forgotusername'];
	return !in_array(adminPageRequest(), $accountPages);
}
function adminIsLogin()
{
	return adminPageRequest() === 'login';
}
function adminIsResetPassword()
{
	return adminPageRequest() === 'resetpassword';
}
function adminIsRegister()
{
	return adminPageRequest() === 'register';
}
function adminIsForgotPassword()
{
	return adminPageRequest() === 'forgotpassword';
}
function adminIsForgotUsername()
{
	return adminPageRequest() === 'forgotusername';
}
function adminIsTaxonomy()
{
	return adminPageRequest() === 'taxonomy';
}
function adminIsComments()
{
	return adminPageRequest() === 'comments';
}
function adminIsWriter()
{
	return adminPageRequest() === 'writer';
}
function adminIsSettings()
{
	return adminPageRequest() === 'settings';
}
function adminIsArticles()
{
	return adminPageRequest() === 'articles';
}
function adminActiveTabName()
{
	$env = \Kanso\Kanso::getInstance()->Environment;
	$url = $env['REQUEST_URL'];
	
	if (adminIsSettings()) {
		
		$request = str_replace($env['HTTP_HOST'].'/admin/settings/', "", $url);
		
		return trim($request, '/');
	}
	else {
		$request = str_replace($env['HTTP_HOST'].'/admin/', "", $url);
		return trim($request, '/');
	}
}
function adminAllUsers() 
{
	return \Kanso\Kanso::getInstance()->Database->Builder()->SELECT("*")->FROM('users')->FIND_ALL();
}
function adminPostTypes()
{
	$types = ['post', 'page'];
	return \Kanso\Filters::apply('adminPostTypes', $types);
}







