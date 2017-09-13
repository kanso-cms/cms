<?php 
use kanso\framework\utility\Humanizer;
use kanso\framework\utility\Str;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo the_title();?></title>
	<style>
		/* RESET */
		html{color:#000;background:#FFF}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0}table{border-collapse:collapse;border-spacing:0}fieldset,img{border:0}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal}ol,ul{list-style:none}caption,th{text-align:left}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal}q:before,q:after{content:''}abbr,acronym{border:0;font-variant:normal}sup{vertical-align:text-top}sub{vertical-align:text-bottom}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;*font-size:100%}legend{color:#000}#yui3-css-stamp.cssreset{display:none}
		
		/* HTML/BODY */
		*, :after, :before {
		    -webkit-box-sizing: border-box;
		    box-sizing: border-box;
		}
		html {
		   	font-size: 62.5%;
		}
		body {
		    font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			font-size: 1.45rem;
		}
		html,body {
			background: #f7f7f7;
		    color: #2b2b2b;
		    -webkit-font-smoothing: antialiased;
	        -moz-osx-font-smoothing: grayscale;
		}

		/* LISTS */
		ul {
		    display: block;
		    list-style-type: disc;
		   	margin: 0 0;
		   	padding: 0 0 0 40px;
		}
		li {
		    display: list-item;
		}
		.dl-horizontal dt {
	        float: left;
		    text-align: left;
		    overflow: hidden;
		    text-overflow: ellipsis;
		    white-space: nowrap;
		    width: 110px;
		    clear: left;
		    font-weight: 600;
		}
		.dl-horizontal dd {
		   	margin-left: 120px;
		}
		.dl-horizontal dd:after {
		  content: "";
		  display: table;
		  clear: both;
		}
		.dl-horizontal dd,
		.dl-horizontal dt
		{
			margin-bottom: 5px;
		}

		/* TYPOGRAPHY */
		p {
			margin: 1em 0;
			padding: 0;
		}
		pre, code {
	        font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
		    line-height: 1.8;
		    font-size: 1.2rem;
		    -webkit-font-smoothing: initial;
		    -moz-osx-font-smoothing: initial;
		}
		code {
		    font-size: 1.4rem;
		    color: #717171;
		}
		p code {
			padding: .2rem .4rem;
		}
		a {
		    color: rgb(17, 85, 204);
		    text-decoration: none;
		}
		strong {
			font-weight: 600;
		}
		h1, h2, h3, h4, h5, h6 {
			color: #333;
			margin-bottom: 16px;
			font-weight: normal;
		}
		h1 {
		    font-size: 3rem;
		}
		h2 {
		    font-size: 2rem;
		}
		h3 {
		    font-size: 1.8rem;
		}
		h4 {
		    font-size: 1.6rem;
		}
		h5 {
		    font-size: 1.4rem;
		}
		h6 {
		    font-size: 1.3rem;
		}
		.uppercase {
			text-transform: uppercase;
		}

		/* BUTTON */
		button,
		.button {
		  background: rgb(66, 133, 244);
		  border: 0;
		  border-radius: 2px;
		  color: #fff;
		  cursor: pointer;
		  font-size: .875em;
		  margin: 0;
		  padding: 18px 24px;
		  transition: box-shadow 200ms cubic-bezier(0.4, 0, 0.2, 1);
		  font-weight: bold;
		  user-select: none;
		  display: inline-block;
		}
		button:active,
		.button:active {
		  background: rgb(50, 102, 213);
		  outline: 0;
		}
		button:hover,
		.button:hover {
		  box-shadow: 0 1px 3px rgba(0, 0, 0, .50);
		}

		/* LAYOUT */
		.row {
			width: 100%;
			display: block;
			padding-top: 15px;
			padding-bottom: 15px;
		}
		.row:after {
		  content: "";
		  display: table;
		  clear: both;
		}
		.row + .row
		{
			padding-top: 0;
		}
		.interstitial-wrapper {
			width: 100%;
			max-width: 680px;
		    padding-top: 100px;
		    margin: 0 auto;
		    margin-bottom: 90px;
		    overflow: hidden;
		    padding-left: 10px;
		    padding-right: 10px;
		}

		/* STYLES */
		.attachment
		{
			width: 100%;
		    height: auto;
		    max-width: 450px;
		    border: 1px solid #343434;
		}
		.body-copy
		{
		    color: #646464;
		    font-size: 1.3rem;
		}
	</style>
</head>
	<body>
		<div class="interstitial-wrapper">
			<div class="content">
				<h1><?php echo the_title();?></h1>
				<?php if (the_attachment()->isImage() || the_attachment()->ext() === 'svg') : ?>
				<img class="attachment" height="<?php echo the_attachment()->height(the_attachment_size());?>" width="<?php echo the_attachment()->width(the_attachment_size());?>" src="<?php echo the_post_thumbnail_src(null, the_attachment_size());?>" alt="<?php echo the_attachment()->alt;?>" title="<?php echo the_attachment()->title;?>" />
				<?php else : ?>
					<img class="attachment" height="300" width="300" src="<?php echo the_attachments_url();?>/no-preview-available.jpg" alt="No preivew available" title="<?php echo the_attachment()->title;?>" />
				<?php endif;?>

				<div class="body-copy row">
					<p>By <?php echo $kanso->Config->get('cms.route_authors') ? '<a title="View All Posts By '.the_author_name().'" href="'.the_author_url().'">'.the_author_name().'</a>' : the_author_name();?> on <time datetime="<?php echo the_time('c'); ?>"><?php echo the_time('l, F d, Y \a\t h:ia'); ?></time></p>
					<p>
						<?php echo the_excerpt();?>
					</p>
					<dl class="dl-horizontal">
						<dt>Title:</dt>
					    <dd><?php echo the_title();?></dd>
						
						<dt>File Name:</dt>
					    <dd><?php echo Str::getAfterLastChar(trim($kanso->Request->environment()->REQUEST_URL, '/'), '/');?></dd>
					    
					    <?php if (the_attachment()->isImage()) : ?>
					    <dt>Full Size:</dt>
					    <dd><a title="View This Image At Full Resolution" href="<?php echo the_attachment_url();?>"><?php echo the_attachment_url();?></a></dd>
					    <?php endif;?>

					    <dt>Uploaded:</dt>
					    <dd><time datetime="<?php echo the_time('c'); ?>"><?php echo the_time('d/m/Y'); ?></time></dd>
					    
					    <dt>Uploaded By:</dt>
					    <dd><?php echo the_author_name();?></dd>
					    
					    <dt>Size:</dt>
					    <dd><?php echo Humanizer::fileSize(the_attachment()->size);?></dd>

					    <?php if (the_attachment()->isImage() || the_attachment()->ext() === 'svg') : ?>
					    <dt>Dimensions:</dt>
					    <dd><?php echo the_attachment()->height(the_attachment_size());?> x <?php echo the_attachment()->width(the_attachment_size());?></dd>
						<?php endif;?>

						<?php if (the_attachment()->isImage()) : ?>
					    <dt>Available Sizes:</dt>
					    <dd>
					    	<a href="<?php echo the_post_thumbnail_src();?>"><?php echo the_attachment()->height();?> x <?php echo the_attachment()->width();?></a>
					    	&nbsp;•&nbsp;
					    	<?php 
					    	$sizes = $kanso->Config->get('cms.uploads.thumbnail_sizes');
					    	foreach ($sizes as $name => $size) : ?>
							<a href="<?php echo the_post_thumbnail_src(null, $name);?>"><?php echo is_array($size) ? $size[0].' x '.$size[1] : $size; ?></a>
							<?php echo ($size === end($sizes)) ? '' : '&nbsp;•&nbsp;';?>
							<?php endforeach;?>
					    </dd>
						<?php endif; ?>
					</dl>
				</div>
				<div class="row">
					<a class="button" title="View This Image At Full Resolution" href="<?php echo the_attachment_url();?>"><?php echo (the_attachment()->isImage() || the_attachment()->ext() === 'svg') ? 'View Source' : 'Download File';?></a>
				</div>
			</div>
		</div>
	</body>
</html>