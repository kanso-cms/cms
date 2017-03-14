<?php

/**
 * Kanso Default Settings
 *
 * When Kanso is first installed or restored to factory settings,
 * the settings in this file are used to setup the default
 * tables in the database as well as populate them with a few 
 * examples.
 */

# Default admin security keys for new user
$keys = [
	'access_token' => 'XGrcqMWdk62rjMKag6HZkueZn3K7d6PuqMramQ==',
];

# Hashed default admin password
$hashed = \Kanso\Security\Encrypt::hash($this->config['KANSO_OWNER_PASSWORD']);

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
	'category_id' => 'INTEGER | UNSIGNED',
	'thumbnail'   => 'VARCHAR(255)',
	'comments_enabled' => 'BOOLEAN DEFAULT FALSE',
];

# Default tags table
$KANSO_DEFAULTS_TAGS_TABLE = [
	'id'   => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'name' => 'VARCHAR(255)',
	'slug' => 'VARCHAR(255)',
];

# Default categories table
$KANSO_DEFAULTS_CATEGORIES_TABLE = [
	'id'   => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'name' => 'VARCHAR(255)',
	'slug' => 'VARCHAR(255)',
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
	'thumbnail'    => 'VARCHAR(255)',
	'role'         => 'VARCHAR(255)',
	'description'  => 'VARCHAR(255)',
	'status'       => 'VARCHAR(255)',
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
	'content'  	   => 'VARCHAR(255)',
	'html_content' => 'VARCHAR(255)',
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

# Default content to articles table
# Note article content is stored seperately from the 
# article entry to improve database performance
$KANSO_DEFAULTS_CONTENT_TO_POSTS_TABLE = [
	'id' 	   => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'content'  => 'TEXT',
	'post_id'  => 'INTEGER | UNSIGNED',
];

# The default user entry
$KANSO_DEFAULT_USER = [
	"username"    		 => $this->config['KANSO_OWNER_USERNAME'],
	"email"       		 => $this->config['KANSO_OWNER_EMAIL'],
	"hashed_pass" 		 => utf8_encode($hashed),
	"name"        		 => 'John Appleseed',
	"slug"        		 => 'john-appleseed',
	"facebook"    		 => 'https://www.facebook.com/example',
	"twitter"     		 => 'https://www.twitter.com/example',
	"gplus"       		 => 'https://www.plus.google.com/example',
	"thumbnail"  		 => 'author_img_large.png',
	"status"      		 => 'confirmed',
	"role"       		 => 'administrator',
	"description" 		 => 'This is where your author bio goes. You can put a small description about yourself here, or just leave it blank if you like.',
	"access_token"       => $keys['access_token'],
];

# The default tags entries
$KANSO_DEFAULT_TAGS = [
	[
		'name' => 'Untagged',
		'slug' => 'untagged'
	],
	[
		'name' => 'HTML',
		'slug' => 'html'
	],
	[
		'name' => 'CSS',
		'slug' => 'css'
	],
	[
		'name' => 'JavaScript',
		'slug' => 'javascript'
	],
];

# The default categories entries
$KANSO_DEFAULT_CATEGORIES = [
	[
		'name' => 'Uncategorized',
		'slug' => 'uncategorized'
	],
	[
		'name' => 'HTML',
		'slug' => 'html'
	],
	[
		'name' => 'CSS',
		'slug' => 'css'
	],
	[
		'name' => 'JavaScript',
		'slug' => 'javascript'
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
		'category_id' => 2,
		'thumbnail'   => 'hero1_large.jpg',
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
		'category_id' => 2,
		'thumbnail'   => 'hero2_large.jpg',
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
		'category_id' => 3,
		'thumbnail'   => 'hero3_large.jpg',
		'comments_enabled' => true,
	],
];

# The default article content entries
$KANSO_DEFAULT_ARTICLE_CONTENT = [
	file_get_contents(__DIR__.'/HelloWorld.md'),
	file_get_contents(__DIR__.'/MarkdownBasics.md'),
	file_get_contents(__DIR__.'/Elements.md'),
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