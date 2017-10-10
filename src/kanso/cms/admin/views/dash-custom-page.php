<!-- PAGE WRAP -->
<div class="dash-wrap js-dash-wrap">

	<!-- HEADING -->
	<section class="page-heading">
		<h1><?php echo trim(explode('|', admin_the_title())[0]); ?></h1>
	</section>

	<!-- POST MESSAGE -->
	<?php require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'post-message.php'); ?>

	<!-- CUSTOM PAGE CONTENT -->
	<?php
		$template = $kanso->Filters->apply('adminPageTemplate', admin_page_name());

		if (file_exists($template))
		{
			require_once($template);
		}
	?>
	
</div>
