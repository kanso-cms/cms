
	<footer id="footer">
	</footer>

</div>

<div class="global-progress js-global-progress">
	<span class="progress"></span>
</div>

<div class="global-spinner js-global-spinner">
	<span class="spinner1"></span>
	<span class="spinner2"></span>
</div>

<div class="nofif-wrap js-nofification-wrap"></div>
	
	<?php if ( $ADMIN_PAGE_TYPE === 'writer' ||  $ADMIN_PAGE_TYPE === 'settings' ) : ?>
	<script type="text/javascript" src="<?php echo $KANSO_ADMIN_ASSETS;?>js/libs/dropzone.js?v=<?php echo $KANSO_VERSION;?>"></script>
	<?php endif;?>

	<script type="text/javascript" src="<?php echo $KANSO_ADMIN_ASSETS;?>js/scripts.js?v=<?php echo $KANSO_VERSION;?>"></script>

	<?php if ($ADMIN_PAGE_TYPE === 'writer') : ?>
	<script type="text/javascript" src="<?php echo $KANSO_ADMIN_ASSETS;?>js/writer.js?v=<?php echo $KANSO_VERSION;?>"></script>
	<?php endif;?>

</body>
</html>