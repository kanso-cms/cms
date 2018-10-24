<!-- PAGE WRAP -->
<div class="dash-wrap js-dash-wrap">

	<!-- HEADING -->
	<section class="page-heading">
		<h1>Error Logs</h1>
	</section>

	<?php require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'post-message.php'); ?>

	<!-- LIST -->
	<section class="items-list">

		<!-- LIST POWERS -->
		<?php require 'templates' . DIRECTORY_SEPARATOR . 'error-logs' . DIRECTORY_SEPARATOR . 'list-powers.php'; ?>

		<!-- LIST BODY -->
		<?php require 'templates' . DIRECTORY_SEPARATOR . 'error-logs' . DIRECTORY_SEPARATOR . 'list-body.php'; ?>

		<!-- LIST FOOTER -->
		<?php require 'templates' . DIRECTORY_SEPARATOR . 'error-logs' . DIRECTORY_SEPARATOR . 'list-pagination.php'; ?>

	</section>
	
</div>

