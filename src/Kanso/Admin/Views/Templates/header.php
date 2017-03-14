<!DOCTYPE html>
<html lang="en">
<head>

	<!-- META -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $ADMIN_INCLUDES->theTitle();?></title>

	<!-- FAVICONS -->
	<?php echo $ADMIN_INCLUDES->favicons(); ?>

	<!-- SCRIPTS/STYLES -->
	<?php echo $ADMIN_INCLUDES->headerScripts(); ?>

</head>
<body>

<!-- LOGO SPRITE-->
<svg width="512" height="512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: none;">',
	<g id="logo"><path fill="#62C4B6" d="M297.011,22.469c0,9.491-7.765,17.256-17.256,17.256H153.851c-9.491,0-17.255-7.765-17.255-17.256v-2.551c0-9.491,7.764-17.256,17.255-17.256h125.904c9.491,0,17.256,7.765,17.256,17.256V22.469z"/><path fill="#62C4B6" d="M404.228,88.021c0,9.491-7.766,17.256-17.256,17.256H110.73c-9.491,0-17.255-7.765-17.255-17.256v-2.55c0-9.492,7.764-17.257,17.255-17.257h276.242c9.49,0,17.256,7.765,17.256,17.257V88.021z"/><path fill="#62C4B6" d="M460.526,153.573c0,9.491-7.767,17.255-17.256,17.255H112.456c-9.492,0-17.256-7.764-17.256-17.255v-2.551c0-9.492,7.764-17.256,17.256-17.256H443.27c9.489,0,17.256,7.765,17.256,17.256V153.573z"/><path fill="#35495E" d="M404.228,211.757c0,9.489-7.766,17.256-17.256,17.256H276.566c-9.491,0-17.257-7.768-17.257-17.256v-2.554c0-9.49,7.766-17.254,17.257-17.254h110.406c9.49,0,17.256,7.764,17.256,17.254V211.757z"/><path fill="#35495E" d="M434.91,267.817c0,9.492-7.766,17.259-17.255,17.259h-78.968c-9.488,0-17.254-7.767-17.254-17.259v-2.548c0-9.49,7.766-17.255,17.254-17.255h78.968c9.489,0,17.255,7.765,17.255,17.255V267.817z"/><path fill="#35495E" d="M425.146,323.885c0,9.493-7.767,17.259-17.257,17.259H186.136c-9.491,0-17.255-7.766-17.255-17.259v-2.555c0-9.488,7.765-17.254,17.255-17.254h221.753c9.49,0,17.257,7.766,17.257,17.254V323.885z"/><path fill="#F5B54C" d="M410.804,379.95c0,9.488-7.766,17.259-17.255,17.259H112.454c-9.491,0-17.256-7.771-17.256-17.259v-2.552c0-9.488,7.766-17.255,17.256-17.255h281.095c9.489,0,17.255,7.767,17.255,17.255V379.95z"/><path fill="#FFD061" d="M381.722,436.018c0,9.488-7.767,17.255-17.256,17.255H141.538c-9.491,0-17.254-7.767-17.254-17.255v-2.556c0-9.491,7.764-17.257,17.254-17.257h222.928c9.489,0,17.256,7.766,17.256,17.257V436.018z"/><path fill="#FFD061" d="M340.821,492.08c0,9.491-7.765,17.258-17.255,17.258H182.44c-9.49,0-17.254-7.767-17.254-17.258v-2.553c0-9.488,7.764-17.254,17.254-17.254h141.125c9.49,0,17.255,7.766,17.255,17.254V492.08z"/><path fill="#62C4B6" d="M234.892,219.125c0,9.491-7.766,17.257-17.257,17.257H115.601c-9.491,0-17.255-7.766-17.255-17.257v-2.551c0-9.49,7.764-17.256,17.255-17.256h102.034c9.491,0,17.257,7.766,17.257,17.256V219.125z"/></g>
	<g id="logo-white"><path fill="#fff" d="M297.011,22.469c0,9.491-7.765,17.256-17.256,17.256H153.851c-9.491,0-17.255-7.765-17.255-17.256v-2.551c0-9.491,7.764-17.256,17.255-17.256h125.904c9.491,0,17.256,7.765,17.256,17.256V22.469z"/><path fill="#fff" d="M404.228,88.021c0,9.491-7.766,17.256-17.256,17.256H110.73c-9.491,0-17.255-7.765-17.255-17.256v-2.55c0-9.492,7.764-17.257,17.255-17.257h276.242c9.49,0,17.256,7.765,17.256,17.257V88.021z"/><path fill="#fff" d="M460.526,153.573c0,9.491-7.767,17.255-17.256,17.255H112.456c-9.492,0-17.256-7.764-17.256-17.255v-2.551c0-9.492,7.764-17.256,17.256-17.256H443.27c9.489,0,17.256,7.765,17.256,17.256V153.573z"/><path fill="#fff" d="M404.228,211.757c0,9.489-7.766,17.256-17.256,17.256H276.566c-9.491,0-17.257-7.768-17.257-17.256v-2.554c0-9.49,7.766-17.254,17.257-17.254h110.406c9.49,0,17.256,7.764,17.256,17.254V211.757z"/><path fill="#fff" d="M434.91,267.817c0,9.492-7.766,17.259-17.255,17.259h-78.968c-9.488,0-17.254-7.767-17.254-17.259v-2.548c0-9.49,7.766-17.255,17.254-17.255h78.968c9.489,0,17.255,7.765,17.255,17.255V267.817z"/><path fill="#fff" d="M425.146,323.885c0,9.493-7.767,17.259-17.257,17.259H186.136c-9.491,0-17.255-7.766-17.255-17.259v-2.555c0-9.488,7.765-17.254,17.255-17.254h221.753c9.49,0,17.257,7.766,17.257,17.254V323.885z"/><path fill="#fff" d="M410.804,379.95c0,9.488-7.766,17.259-17.255,17.259H112.454c-9.491,0-17.256-7.771-17.256-17.259v-2.552c0-9.488,7.766-17.255,17.256-17.255h281.095c9.489,0,17.255,7.767,17.255,17.255V379.95z"/><path fill="#fff" d="M381.722,436.018c0,9.488-7.767,17.255-17.256,17.255H141.538c-9.491,0-17.254-7.767-17.254-17.255v-2.556c0-9.491,7.764-17.257,17.254-17.257h222.928c9.489,0,17.256,7.766,17.256,17.257V436.018z"/><path fill="#fff" d="M340.821,492.08c0,9.491-7.765,17.258-17.255,17.258H182.44c-9.49,0-17.254-7.767-17.254-17.258v-2.553c0-9.488,7.764-17.254,17.254-17.254h141.125c9.49,0,17.255,7.766,17.255,17.254V492.08z"/><path fill="#fff" d="M234.892,219.125c0,9.491-7.766,17.257-17.257,17.257H115.601c-9.491,0-17.255-7.766-17.255-17.257v-2.551c0-9.49,7.764-17.256,17.255-17.256h102.034c9.491,0,17.257,7.766,17.257,17.256V219.125z"/></g>
</svg>

