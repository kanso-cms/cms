<!DOCTYPE html>
<html lang="en">
<head>

	<!-- HTML -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php the_page_title();?></title>

	<!-- FAVICONS -->
	<link rel="shortcut icon"                    href="<?php theme_url(); ?>/assets/images/favicon.png">
	<link rel="apple-touch-icon" sizes="57x57"   href="<?php theme_url(); ?>/assets/images/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72"   href="<?php theme_url(); ?>/assets/images/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="<?php theme_url(); ?>/assets/images/apple-touch-icon-114x114.png">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php theme_url(); ?>/assets/css/style.css">

</head>
<body>

<!-- SITE HEADER -->
<div class="banner">
	<div class="container">
		<div class="header clear">
			<input type="checkbox" class="menu-checkbox" id="navigation" name="navigation">
			<label class="menu-label animated fade-in-right" for="navigation" id="toggle-menu">
				<span class="menu-label-inner">
					<span class="menu-label-open">Menu</span>
					<span class="menu-label-close">Close</span>
				</span>
			</label>
			<a class="logo" href="/"><span class="h1 font-900">Kanso</span></a>
			<div class="navigation" id="menu">
				<div class="navigation-inner container-full">
					<div class="menu container">
						<div class="clear row">
							<div class="col-4-tablet">
								<strong class="menu-heading"><a class="menu-heading-link" href="/">Home</a></strong>
								<ul class="menu-secondary">
									<li class="menu-item"><a href="https://github.com/joey-j/Kanso/archive/master.zip" class="menu-item-link">Download Latest Build</a></li>
									<li class="menu-item"><a href="https://github.com/joey-j/Kanso" class="menu-item-link">View Source on Github</a></li>
									<li class="menu-item"><a href="https://github.com/joey-j/Kanso/issues" class="menu-item-link">Current Issues</a></li>
								</ul>
							</div>
							<div class="col-4-tablet">
								<strong class="menu-heading"><a class="menu-heading-link active" href="http://pencilscoop.com/Kanso/documentation">Documentation</a></strong>
								<ul class="menu-secondary">
									<li class="menu-item"><a href="http://pencilscoop.com/Kanso/documentation" class="menu-item-link">Introduction</a></li>
									<li class="menu-item"><a href="http://pencilscoop.com/Kanso/documentation" class="menu-item-link">HTML Template</a></li>
									<li class="menu-item"><a href="http://pencilscoop.com/Kanso/documentation" class="menu-item-link">LESS/SCSS/CSS</a></li>
									<li class="menu-item"><a href="http://pencilscoop.com/Kanso/documentation" class="menu-item-link">Assets</a></li>
									<li class="menu-item"><a href="http://pencilscoop.com/Kanso/documentation" class="menu-item-link">Helpers</a></li>
									<li class="menu-item"><a href="http://pencilscoop.com/Kanso/documentation" class="menu-item-link">The Grid</a></li>
									<li class="menu-item"><a href="http://pencilscoop.com/Kanso/documentation" class="menu-item-link">Mixins</a></li>
									<li class="menu-item"><a href="http://pencilscoop.com/Kanso/documentation" class="menu-item-link">Animations</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- CONTENT -->
<div class="content container">
	<div class="content-inner">