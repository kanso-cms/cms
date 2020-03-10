<!-- PAGE WRAP -->
<div class="dash-wrap js-dash-wrap <?php echo strtolower($postName); ?>">

	<!-- HEADING -->
	<section class="page-heading">
		<h1><?php echo $postName; ?></h1>
		<a href="/admin/writer/?post-type=<?php echo strtolower($postType); ?>" class="btn btn-primary add-btn">
			<span class="glyph-icon glyph-icon-plus"></span>&nbsp;&nbsp;&nbsp;Add New <?php echo ucfirst($postType); ?></a>
	</section>

	<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'post-message.php'); ?>

	<!-- LIST -->
	<section class="items-list">

		<!-- LIST POWERS -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'list-powers.php'); ?>

		<!-- LIST BODY -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'list-body.php'); ?>

		<!-- LIST FOOTER -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'list-pagination.php'); ?>

	</section>

</div>