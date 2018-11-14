<!-- PAGE WRAP -->
<div class="dash-wrap js-dash-wrap">

	<!-- HEADING -->
	<section class="page-heading">
		<h1>Commentors</h1>
	</section>

	<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'post-message.php'); ?>

	<!-- TABS -->
	<div class="floor-sm">
		<ul class="tab-nav tab-border">
			<li><a href="/admin/leads/">Leads</a></li>
			<li><a href="/admin/comments/">Comments</a></li>
		    <li><a href="/admin/comment-users/" class="active">Commentors</a></li>
		</ul>
	</div>
	
	<!-- LIST -->
	<section class="items-list">

		<!-- LIST POWERS -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'commentusers' . DIRECTORY_SEPARATOR . 'list-powers.php'); ?>

		<!-- LIST BODY -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'commentusers' . DIRECTORY_SEPARATOR . 'list-body.php'); ?>

		<!-- LIST FOOTER -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'commentusers' . DIRECTORY_SEPARATOR . 'list-pagination.php'); ?>

	</section>

</div>