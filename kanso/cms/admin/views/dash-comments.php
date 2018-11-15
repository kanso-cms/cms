<!-- PAGE WRAP -->
<div class="dash-wrap js-dash-wrap">

	<!-- HEADING -->
	<section class="page-heading">
		<h1>Comments</h1>
	</section>

	<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'post-message.php'); ?>

	<div class="floor-sm">
		<ul class="tab-nav tab-border ">
			<li><a href="/admin/leads/">Leads</a></li>
			<li><a href="/admin/comments/" class="active">Comments</a></li>
		    <li><a href="/admin/comment-users/">Commentors</a></li>
		</ul>
	</div>

	<!-- LIST -->
	<section class="items-list">

		<!-- LIST POWERS -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'comments' . DIRECTORY_SEPARATOR . 'list-powers.php'); ?>

		<!-- LIST BODY -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'comments' . DIRECTORY_SEPARATOR . 'list-body.php'); ?>

		<!-- LIST FOOTER -->
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'comments' . DIRECTORY_SEPARATOR . 'list-pagination.php'); ?>

	</section>

</div>