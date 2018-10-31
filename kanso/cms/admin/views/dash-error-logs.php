<!-- PAGE WRAP -->
<div class="dash-wrap js-dash-wrap">

	<!-- HEADING -->
	<section class="page-heading">
		<h1>Error Logs</h1>
	</section>

	<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'post-message.php'); ?>

	<!-- TAB NAV -->
	<?php require 'templates' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'tab-nav.php'; ?>

	<!-- LIST -->
	<section class="items-list roof-xs">

		<!-- LIST POWERS -->
		<?php require 'templates' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'list-powers.php'; ?>

		<!-- LIST BODY -->
		<?php require 'templates' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'list-body.php'; ?>

		<!-- LIST FOOTER -->
		<?php require 'templates' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'list-pagination.php'; ?>

	</section>
	
</div>