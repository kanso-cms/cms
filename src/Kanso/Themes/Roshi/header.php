<?php
/**
 * Header template file
 *
 * This is the template file for the header.
 * This template will be loaded whenever the_header() is called.
 *
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>

	<!-- HTML META -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo the_meta_title();?></title>
	<meta name="description" content="<?php echo the_meta_description();?>">

	<link rel="canonical" title="<?php echo the_meta_title();?>"          href="<?php echo the_canonical_url();?>">
	<link rel="prev"      title="<?php echo the_previous_page_title();?>" href="<?php echo the_previous_page_url();?>">
	<link rel="next"      title="<?php echo the_next_page_title();?>"     href="<?php echo the_next_page_url();?>">

	<!-- FAVICONS -->
	<link rel="shortcut icon"                    href="<?php echo theme_url(); ?>/assets/images/favicon.png">
	<link rel="apple-touch-icon" sizes="57x57"   href="<?php echo theme_url(); ?>/assets/images/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72"   href="<?php echo theme_url(); ?>/assets/images/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo theme_url(); ?>/assets/images/apple-touch-icon-114x114.png">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo theme_url(); ?>/assets/css/style.css">

</head>
<body>

<!-- SITE HEADER -->
<header class="clearfix row">
	
	<div class="content container row">
		<ul class="list-inline left">
			<li>
				<a href="<?php echo home_url();?>"><h1>Roshi</h1></a>
				<span class="info-text">Default theme for Kanso CMS.</span>
			</li>
		</ul>
		<div class="clearfix"></div>
		<hr>
	</div>
</header>


<!-- CONTENT -->
<div class="content container clearfix">