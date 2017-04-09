<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<entry>
		<author>
			<name><?php echo the_author_name();?></name>
			<uri><?php echo the_author_url();?></uri>
		</author>
		<title type="html"><?php echo '<![CDATA['.the_title().']]>';?></title>
		<link rel="alternate" type="text/html" href="<?php echo the_permalink();?>" />
		<id><?php echo the_post_id();?></id>
		<updated><?php echo the_modified_time('c');?></updated>
		<published><?php echo the_time('c'); ?></published>
		<category scheme="<?php echo home_url(); ?>" term="<?php echo the_category_name(); ?>" />
		<summary type="html"><?php echo '<![CDATA['.the_excerpt().']]>';?></summary>
		<content type="html" xml:base="<?php echo the_permalink();?>"><?php echo '<![CDATA['.the_content().']]>';?></content>
		<link rel="replies" type="text/html" href="<?php echo the_permalink();?>#comments" thr:count="<?php echo comments_number();?>"/>
		<link rel="replies" type="application/atom+xml" href="<?php echo comments_number();?>feed/atom/" thr:count="<?php echo comments_number();?>"/>
		<thr:total><?php echo comments_number();?></thr:total>
	</entry>
<?php endwhile; endif; ?>