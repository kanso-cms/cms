<?php

# Hashed default admin password
$hashed = \kanso\Kanso::instance()->Crypto->password()->hash($this->config->get('cms.default_password'));

# Default articles table
$KANSO_DEFAULTS_POSTS_TABLE = [
	'id'          => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'created'     => 'INTEGER | UNSIGNED',
	'modified'    => 'INTEGER | UNSIGNED',
	'status'      => 'VARCHAR(255)',
	'type'        => 'VARCHAR(255)',
	'slug'        => 'VARCHAR(255)',
	'title'       => 'VARCHAR(255)',
	'excerpt'     => 'TEXT',
	'author_id'   => 'INTEGER | UNSIGNED',
	'thumbnail_id'     => 'INTEGER | UNSIGNED',
	'comments_enabled' => 'BOOLEAN DEFAULT FALSE',
];

# Default post meta table
$KANSO_DEFAULTS_POST_META_TABLE = [
	'id' 	   => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'content'  => 'TEXT',
	'post_id'  => 'INTEGER | UNSIGNED',
];

# Default tags table
$KANSO_DEFAULTS_TAGS_TABLE = [
	'id'          => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'name'        => 'VARCHAR(255)',
	'slug'        => 'VARCHAR(255)',
	'description' => 'TEXT',
];

# Default categories table
$KANSO_DEFAULTS_CATEGORIES_TABLE = [
	'id'          => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'name'        => 'VARCHAR(255)',
	'slug'        => 'VARCHAR(255)',
	'description' => 'TEXT',
	'parent_id'   => 'INTEGER | UNSIGNED',
];

# Default authors table
$KANSO_DEFAULTS_USERS_TABLE = [
	'id'           => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'username'     => 'VARCHAR(255)',
	'email'        => 'VARCHAR(255)',
	'hashed_pass'  => 'VARCHAR(255)',
	'name'         => 'VARCHAR(255)',
	'slug'         => 'VARCHAR(255)',
	'facebook'     => 'VARCHAR(255)',
	'twitter'      => 'VARCHAR(255)',
	'gplus'        => 'VARCHAR(255)',
	'instagram'    => 'VARCHAR(255)',
	'thumbnail_id' => 'INTEGER | UNSIGNED',
	'role'         => 'VARCHAR(255)',
	'description'  => 'TEXT',
	'status'       => 'VARCHAR(255)',
	'last_online'  => 'VARCHAR(255)',
	'email_notifications' => 'BOOLEAN | DEFAULT TRUE',
	'access_token'        => 'VARCHAR(255)',
	'kanso_register_key'  => 'VARCHAR(255)',
	'kanso_password_key'  => 'VARCHAR(255)',
];

# Default comments table
$KANSO_DEFAULTS_COMMENTS_TABLE = [
	'id'           => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'parent'  	   => 'INTEGER | UNSIGNED',
	'post_id'      => 'INTEGER | UNSIGNED',
	'date'         => 'INTEGER | UNSIGNED',
	'type'         => 'VARCHAR(255)',
	'status'       => 'VARCHAR(255)',
	'name'     	   => 'VARCHAR(255)',
	'email'    	   => 'VARCHAR(255)',
	'content'  	   => 'TEXT',
	'html_content' => 'TEXT',
	'ip_address'   => 'VARCHAR(255)',
	'email_reply'  => 'BOOLEAN',
	'email_thread' => 'BOOLEAN',
	'rating'       => 'INTEGER | UNSIGNED',
];

# Default tags to posts table
$KANSO_DEFAULTS_TAGS_TO_POSTS_TABLE = [
	'id' 	  => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'tag_id'  => 'INTEGER | UNSIGNED',
	'post_id' => 'INTEGER | UNSIGNED',
];

# Default tags to posts table
$KANSO_DEFAULTS_CATEGORIES_TO_POSTS_TABLE = [
	'id' 	      => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'category_id' => 'INTEGER | UNSIGNED',
	'post_id'     => 'INTEGER | UNSIGNED',
];

# Default content to articles table
# Note article content is stored seperately from the 
# article entry to improve database performance
$KANSO_DEFAULTS_CONTENT_TO_POSTS_TABLE = [
	'id' 	   => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'content'  => 'TEXT',
	'post_id'  => 'INTEGER | UNSIGNED',
];

# Default media table
$KANSO_DEFAULTS_MEDIA_TABLE = [
	'id'           => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'url'  	       => 'VARCHAR(255)',
	'path'  	   => 'VARCHAR(255)',
	'title'	       => 'VARCHAR(255)',
	'alt'	       => 'VARCHAR(255)',
	'size'         => 'INTEGER | UNSIGNED',
	'dimensions'   => 'VARCHAR(255)',
	'date'         => 'INTEGER | UNSIGNED',
	'uploader_id'  => 'INTEGER | UNSIGNED',
];

# The default user entry
$KANSO_DEFAULT_USER = [
	"username"    		 => $this->config->get('cms.default_username'),
	"email"       		 => $this->config->get('cms.default_email'),
	"hashed_pass" 		 => utf8_encode($hashed),
	"name"        		 => $this->config->get('cms.default_name'),
	"slug"        		 => 'john-appleseed',
	"facebook"    		 => 'https://www.facebook.com/example',
	"twitter"     		 => 'https://www.twitter.com/example',
	"gplus"       		 => 'https://www.plus.google.com/example',
	"instagram"       	 => 'https://www.instagram.com/example',
	"thumbnail_id"       => 1,
	"status"      		 => 'confirmed',
	"role"       		 => 'administrator',
	"description" 		 => 'This is where your author bio goes. You can put a small description about yourself here, or just leave it blank if you like.',
	"access_token"       => 'XGrcqMWdk62rjMKag6HZkueZn3K7d6PuqMramQ==',
];

# The default tags entries
$KANSO_DEFAULT_TAGS = [
	[
		'name'        => 'Untagged',
		'slug'        => 'untagged',
		'description' => '',
	],
	[
		'name' => 'HTML',
		'slug' => 'html',
		'description' => 'Posts tags under HTML.',

	],
	[
		'name'        => 'CSS',
		'slug'        => 'css',
		'description' => 'Posts tags under CSS.',
	],
	[
		'name'        => 'JavaScript',
		'slug'        => 'javascript',
		'description' => 'Posts tags under JavaScript.',
	],
];

# The default categories entries
$KANSO_DEFAULT_CATEGORIES = [
	[
		'name'        => 'Uncategorized',
		'slug'        => 'uncategorized',
		'description' => '',
	],
	[
		'name'        => 'HTML',
		'slug'        => 'html',
		'description' => 'HTML category posts.',
	],
	[
		'name'        => 'CSS',
		'slug'        => 'css',
		'description' => 'CSS category posts.',
	],
	[
		'name'        => 'JavaScript',
		'slug'        => 'javascript',
		'description' => 'JavaScript category posts.',
	],
];

# The default articles entries
$KANSO_DEFAULT_ARTICLES = [
	[
		'created'     => strtotime('-1 hour'),
		'modified'    => time(),
		'status'      => 'published',
		'type'        => 'post',
		'slug'        => date('Y/m/').'hello-world/',
		'title'       => 'Hello World!',
		'excerpt'     => 'Welcome to Kanso. This is your first post. Edit or delete it, then start blogging!',
		'author_id'   => 1,
		'thumbnail_id'   => 2,
		'comments_enabled' => true,
	],
	[
		'created'     => strtotime('-1 month'),
		'modified'    => time(),
		'status'      => 'published',
		'type'        => 'post',
		'slug'        => date('Y/m/').'markdown-basics/',
		'title'       => 'Markdown Basics',
		'excerpt'     => 'This is intended as a quick reference and showcase.Kanso uses Markdown Extra to parse article content. Content written within the Kanso Writer application is stored in the database as raw text. When the article content is loaded, it is parsed using ParseDown Extra.',
		'author_id'   => 1,
		'thumbnail_id'     => 3,
		'comments_enabled' => true,
	],
	[
		'created'     => strtotime('-1 year'),
		'modified'    => time(),
		'status'      => 'published',
		'type'        => 'post',
		'slug'        => date('Y/m/').'elements/',
		'title'       => 'Elements',
		'excerpt'     => 'The purpose of this HTML is to help determine what default settings are with CSS and to make sure that all possible HTML Elements are included in this HTML so as to not miss any possible Elements when designing a site.',
		'author_id'   => 1,
		'thumbnail_id'     => 4,
		'comments_enabled' => true,
	],
];

# The default article content entries
$KANSO_DEFAULT_ARTICLE_CONTENT = [
	file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'HelloWorld.md'),
	file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'MarkdownBasics.md'),
	file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'Elements.md'),
];

# The default comments entries
$KANSO_DEFAULT_COMMENTS = [
	[
		'post_id'      => 1,
		'parent'  	   => null,
		'date'         => strtotime('-1 hour'),
		'type'         => 'comment',
		'status'       => 'approved',
		'name'     	   => 'John Doe',
		'email'    	   => 'JohnDoe@example.com',
		'content'  	   => 'This is a top level comment.',
		'html_content' => '<p>This is a top level comment.</p>',
		'ip_address'   => '192.168.1.1',
		'email_reply'  => false,
		'email_thread' => false,
		'rating'       => -3,
	],
	[
		'post_id'      => 1,
		'parent'  	   => 1,
		'date'         => strtotime('-4 hour'),
		'type'         => 'reply',
		'status'       => 'approved',
		'name'     	   => 'Mark Doe',
		'email'    	   => 'MarkDoe@example.com',
		'content'  	   => 'This is a nested level 2 comment.',
		'html_content' => '<p>This is a nested level 2 comment.<p>',
		'ip_address'   => '192.168.1.2',
		'email_reply'  => false,
		'email_thread' => false,
		'rating'      => -4,
	],
	[
		'post_id'      => 1,
		'parent'  	   => 2,
		'date'         => strtotime('-1 hour'),
		'type'         => 'reply',
		'status'       => 'approved',
		'name'     	   => 'Tim Doe',
		'email'    	   => 'TimDoe@example.com',
		'content'  	   => 'This is a nested level 3 comment.',
		'html_content' => '<p>This is a nested level 3 comment.<p>',
		'ip_address'   => '192.168.1.3',
		'email_reply'  => false,
		'email_thread' => false,
		'rating'       => 2,
	],
];

# The default media library entries
$_imgesDir   = $this->config->get('cms.uploads.path');
$_imagesUrl  = str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['HTTP_HOST'], $_imgesDir);

# localhost bugfix
if (!strpos($_imagesUrl, 'http://') !== false)
{
    $_imagesUrl = 'http://'.$_imagesUrl;
}

$KANSO_DEFAULT_IMAGES = [
	[
		'url'  	       => $_imagesUrl.DIRECTORY_SEPARATOR.'author_img.png',
		'path'  	   => $_imgesDir.DIRECTORY_SEPARATOR.'author_img.png',
		'title'	       => 'Default Author Image',
		'alt'	       => 'Author\'s profile photo',
		'size'         => 443576,
		'dimensions'   => '1200 x 1200',
		'date'         => time(),
		'uploader_id'  => 1,
	],
	[
		'url'  	       => $_imagesUrl.DIRECTORY_SEPARATOR.'hero1.jpg',
		'path'  	   => $_imgesDir.DIRECTORY_SEPARATOR.'hero1.jpg',
		'title'	       => 'New York City',
		'alt'	       => 'Photo of New York City',
		'size'         => 222994,
		'dimensions'   => '1200 x 800',
		'date'         => time(),
		'uploader_id'  => 1,
	],
	[
		'url'  	       => $_imagesUrl.DIRECTORY_SEPARATOR.'hero2.jpg',
		'path'  	   => $_imgesDir.DIRECTORY_SEPARATOR.'hero2.jpg',
		'title'	       => 'Beautiful Landscape with Lake',
		'alt'	       => 'Landscape photo of lake and mountains',
		'size'         =>  140531,
		'dimensions'   => '1200 x 595',
		'date'         => time(),
		'uploader_id'  => 1,
	],
	[
		'url'  	       => $_imagesUrl.DIRECTORY_SEPARATOR.'hero3.jpg',
		'path'  	   => $_imgesDir.DIRECTORY_SEPARATOR.'hero3.jpg',
		'title'	       => 'Beautiful Autumn Leaves',
		'alt'	       => 'Photo of track with autumn leaves',
		'size'         => 222897,
		'dimensions'   => '1200 x 701',
		'date'         => time(),
		'uploader_id'  => 1,
	],
];
