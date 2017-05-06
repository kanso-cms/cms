<!-- ICONS -->
<?php require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Writer'.DIRECTORY_SEPARATOR.'icons.php'); ?>

<!-- PAGE WRAP FOR SIDEBAR NOT USERD -->
<div class="dash-wrap js-dash-wrap hidden"></div>

<!-- CONTAINER -->
<div class="row site-container">
	
	<!-- WRITER -->
	<?php require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Writer'.DIRECTORY_SEPARATOR.'writer.php'); ?>

	<!-- READER -->
	<?php require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Writer'.DIRECTORY_SEPARATOR.'reader.php'); ?>
	
	<!-- REVIEW/PUBLISH -->
	<?php require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Writer'.DIRECTORY_SEPARATOR.'review.php'); ?>

</div>

<!-- FOOTER -->
<?php require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Writer'.DIRECTORY_SEPARATOR.'footer.php'); ?>

<!-- MEDIA LIBRARY -->
<div class="writer-media-wrapper js-triggerable-media">
	<?php require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Media'.DIRECTORY_SEPARATOR.'media-library.php'); ?>
</div>