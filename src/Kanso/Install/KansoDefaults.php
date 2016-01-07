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
	'KANSO_PUBLIC_KEY' 	 => 'jce6sLiexsaWfKrDl8iV3dF4jLzNncCr08Dcm7CoipvlupmK3qrFmNarnKzC8KLe3rOJlZC-4GGUqp2iuZ2MjKKcsqekjeC6lqG1mJbcmsKpmaa-psa_sYbhorWizreEtWd6qJSQsLKnyZ3Cnp6GmpimreG_gp5_1p282r2XxreW73mEu9q_ibmBwXqkhMV76MGdvbmGi32dvNF-eIGEvayfy4_DoLSjpYq_sMzQ3JSpnHXmndfZvoG_sppl5nyyqb23rKdpnpyUudSXsaSp3Jq7jsS9t3a1zZ-SvsOlmM2zgt6kqrppm8PfgZjPoLRl37nLsL7xtdfQloR9sZHAYYmMpry8haei2p7bwcJ9yLer3re5g7t9yKDZu3RnynaRocxyncfItbexf5uYnL_YpIyRibumlYWZzsmN1696hLG5favxzb6ryKrmeni6yJak0JXUmeiZrZTnv7vi6bSBZqilvZ6vfaXhec-agqy1m6Oor92WzdXqlIK5meC8vOazhZKQqoa6eZ_UwpeZjIGKg3_E19WIiru3nLxxhdiYt9SoksSOxJqKp6610Ll8zmWn3d6Ujd6fuZ7gksSW6Mu63KyTZ4V8ks15loeswpGHuGvbnJWnwI7mtd7Aqnuf2aXAu9S6rGOdn5qp0IGwooe9hJmheX19vLuqeY6ixMCrhZmqyZ6vuYiMjMyjns_dtfG7jd9ij-q-wniYsOun4622esXapKKxtH5nmJKsmZZ5lOGYl8Cm2peTsaSFqMi71biVx-CGs67bupBpp3h2pbyns9immpKIfaF8sdDJypOuitu9n2m_2559rdGSkKDFk7GtxajOk5LjmnbJ3bl_17mpgd15t5bG5M3j76eon7WY7J6LparcjrvMe6ya0dHYuqKUqc_qv8ugZKTC1OOLaK54lZ-olXzYoJhyvmaRhcC618exiqLIg5VtxMnLfNTLkqyV2KGZ66LE7JfCvKZ7xr67m76p2GXGrcqkxr3M3cmMb2V9qPCVuGamnYrCuq3Li6_YvHbemNK-xa7K3HjdkLDjqXyyuKqP45aOusqydHiJmoOfn9rPu8mZpp2vsKuYrKm3sXd8oKeajMK1nMWdk62rjMKag6HZkueZn3K7d6PuqMramQ==',
	"KANSO_PUBLIC_SALT"  => 'XdbAYUnLG8ReREhX0KHu2IwkNxQHw2BugM4fHr0nASBrxTpxD72GPy1D6RkDUW7aJbpqDqOdjqFQj4nXcpD1YEF5w0IqWQBF206F',
];

# Hashed default admin password
$hashed = \Kanso\Security\Encrypt::encrypt($this->config['KANSO_OWNER_PASSWORD']);

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
	'KANSO_REGISTER_KEY'  => 'VARCHAR(255)',
	'KANSO_PASSWORD_KEY'  => 'VARCHAR(255)',
	'KANSO_PUBLIC_KEY'    => 'VARCHAR(255)',
	'KANSO_PUBLIC_SALT'   => 'VARCHAR(255)',
	'KANSO_KEYS_TIME'     => 'INTEGER | UNSIGNED',
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
	"kanso_public_key"   => $keys['KANSO_PUBLIC_KEY'],
	"kanso_public_salt"  => $keys['KANSO_PUBLIC_SALT'],
	"kanso_keys_time"    => time(),
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