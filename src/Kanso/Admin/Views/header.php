<!DOCTYPE html>
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml"<?php if (adminIsWriter()) echo ' class="writer-html"';?>>
<head>
	
	<!-- HTML -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title><?php echo adminPageTitle();?></title>

	<!-- FAVICONS -->
	<?php echo adminFavicons(); ?>

	<!-- SCRIPTS -->
	<?php echo adminHeaderScripts(); ?>

</head>
<body data-user-role="<?php echo adminGetUser('role');?>" class="<?php echo adminBodyClass(); ?>">

<!-- SVG SPRITES -->
<?php
echo adminSvgSprites();
?>

<!-- PAGE WRAPPER -->
<div id="wrapper">

<?php if (adminIsDashboard()) : ?>

	<?php if (adminIsWriter()) : ?>
		<a class="show-header js-show-header" href="#">
			<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#hamburger"></use></svg>
		</a>
	<?php endif;?>

	<!-- HEADER -->
	<header class="header <?php echo adminIsWriter() === true ? 'active' : '';?>">

		<div class="site-container clearfix">
			<div class="left col col-6 no-gutter">
				<ul>
					<?php echo adminHeaderLinks(); ?>
				</ul>
			</div>
			<div class="right col col-6 no-gutter text-right">
				<ul>
					<li class="drop-down">
						<a href="#">
							<?php echo adminHeaderName(); ?>
							<?php echo adminHeaderAuthorImg(); ?>
							<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#arrow-down"></use></svg>
						</a>
						<div class="drop">
							<ul>
								<?php echo adminHeaderDropdown(); ?>
							</ul>
						</div>
					</li>
				</ul>
				
				<?php if (adminIsWriter()) : ?>
					<a class="hide-header js-hide-header" href="#">
						<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#hamburger"></use></svg>
					</a>
				<?php endif;?>
				
			</div>
		</div>
	</header>

<?php endif;?>