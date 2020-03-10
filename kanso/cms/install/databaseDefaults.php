<?php

// Hashed default admin password
$hashed = \kanso\Kanso::instance()->Crypto->password()->hash($this->config->get('cms.default_password'));

// Default articles table
$KANSO_DEFAULTS_POSTS_TABLE =
[
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

// Default post meta table
$KANSO_DEFAULTS_POST_META_TABLE =
[
	'id' 	   => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'content'  => 'TEXT',
	'post_id'  => 'INTEGER | UNSIGNED',
];

// Default tags table
$KANSO_DEFAULTS_TAGS_TABLE =
[
	'id'          => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'name'        => 'VARCHAR(255)',
	'slug'        => 'VARCHAR(255)',
	'description' => 'TEXT',
];

// Default categories table
$KANSO_DEFAULTS_CATEGORIES_TABLE =
[
	'id'          => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'name'        => 'VARCHAR(255)',
	'slug'        => 'VARCHAR(255)',
	'description' => 'TEXT',
	'parent_id'   => 'INTEGER | UNSIGNED | DEFAULT 0',
];

// Default authors table
$KANSO_DEFAULTS_USERS_TABLE =
[
	'id'           => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'username'     => 'VARCHAR(255)',
	'visitor_id'   => 'VARCHAR(255)',
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

// Default CRM visitors table
$KANSO_DEFAULTS_VISITORS_TABLE =
[
	'id'            => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'visitor_id'    => 'VARCHAR(255)',
	'ip_address'    => 'VARCHAR(255)',
	'name'          => 'VARCHAR(255)',
	'email'         => 'VARCHAR(255)',
	'made_purchase' => 'BOOLEAN | DEFAULT FALSE',
	'last_active'   => 'INTEGER | UNSIGNED | DEFAULT 0',
	'is_bot'        => 'BOOLEAN | DEFAULT FALSE',
	'user_agent'    => 'VARCHAR(255)',
];

// Default CRM visits table
$KANSO_DEFAULTS_VISITS_TABLE =
[
	'id'           => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'visitor_id'   => 'VARCHAR(255)',
	'ip_address'   => 'VARCHAR(255)',
	'page'         => 'VARCHAR(255)',
	'date'         => 'INTEGER | UNSIGNED | DEFAULT 0',
	'end'          => 'INTEGER | UNSIGNED | DEFAULT 0',
	'medium'       => 'VARCHAR(255)',
	'channel'      => 'VARCHAR(255)',
	'campaign'     => 'VARCHAR(255)',
	'keyword'      => 'VARCHAR(255)',
	'creative'     => 'VARCHAR(255)',
	'browser'      => 'VARCHAR(255)',
];

// Default CRM visits table
$KANSO_DEFAULTS_VISIT_ACTIONS_TABLE =
[
	'id'                 => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'visit_id'           => 'INTEGER | UNSIGNED',
	'visitor_id'         => 'VARCHAR(255)',
	'action_name'        => 'VARCHAR(255)',
	'action_description' => 'VARCHAR(255)',
	'page'               => 'VARCHAR(255)',
	'date'               => 'INTEGER | UNSIGNED | DEFAULT 0',
];

// Default comments table
$KANSO_DEFAULTS_COMMENTS_TABLE =
[
	'id'           => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'parent'  	   => 'INTEGER | UNSIGNED | DEFAULT 0',
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
	'rating'       => 'INTEGER',
];

// Default tags to posts table
$KANSO_DEFAULTS_TAGS_TO_POSTS_TABLE =
[
	'id' 	  => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'tag_id'  => 'INTEGER | UNSIGNED',
	'post_id' => 'INTEGER | UNSIGNED',
];

// Default tags to posts table
$KANSO_DEFAULTS_CATEGORIES_TO_POSTS_TABLE =
[
	'id' 	      => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'category_id' => 'INTEGER | UNSIGNED',
	'post_id'     => 'INTEGER | UNSIGNED',
];

// Default content to articles table
// Note article content is stored seperately from the
// article entry to improve database performance
$KANSO_DEFAULTS_CONTENT_TO_POSTS_TABLE =
[
	'id' 	   => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'content'  => 'TEXT',
	'post_id'  => 'INTEGER | UNSIGNED',
];

// Default media table
$KANSO_DEFAULTS_MEDIA_TABLE =
[
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

// Default media table
$KANSO_DEFAULTS_PAYMENT_TOKENS_TABLE =
[
	'id'           => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'user_id'  	   => 'INTEGER | UNSIGNED',
	'token'  	   => 'VARCHAR(255)',
];

// Default shopping cart items
$KANSO_DEFAULTS_SHOPPING_CART_TABLE =
[
	'id'           => 'INTEGER(11) | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'user_id'  	   => 'INTEGER(11) | UNSIGNED',
	'contents'     => 'TEXT',
];

// Shipping addresses
$KANSO_DEFAULTS_SHIPPING_ADDRESS_TABLE =
[
	'id'               => 'INTEGER(11) | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'user_id'  	       => 'INTEGER(11) | UNSIGNED',
	'email'            => 'VARCHAR(255)',
	'first_name'       => 'VARCHAR(255)',
	'last_name'        => 'VARCHAR(255)',
	'street_address_1' => 'VARCHAR(255)',
	'street_address_2' => 'VARCHAR(255)',
	'suburb'           => 'VARCHAR(255)',
	'zip_code'         => 'VARCHAR(255)',
	'state'            => 'VARCHAR(255)',
	'country'          => 'VARCHAR(255)',
	'telephone'        => 'VARCHAR(255)',
];

// Transaction table
$KANSO_DEFAULTS_TRANSACTION_TABLE =
[
	'id'                => 'INTEGER(11) | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'user_id'  	        => 'INTEGER(11) | UNSIGNED',
	'bt_transaction_id' => 'VARCHAR(255)',
	'shipping_id'       => 'INTEGER(11) | UNSIGNED',
	'date'              => 'INTEGER(11) | UNSIGNED',
	'status'            => 'VARCHAR(255)',
	'shipped'           => 'BOOLEAN | DEFAULT FALSE',
	'shipped_date'      => 'INTEGER(11) | UNSIGNED',
	'tracking_code'     => 'VARCHAR(255)',
	'eta'               => 'INTEGER(11) | UNSIGNED',
	'card_type'         => 'VARCHAR(255)',
	'card_last_four'    => 'INTEGER(11) | UNSIGNED',
	'card_expiry'       => 'VARCHAR(255)',
	'items'             => 'TEXT',
	'sub_total'         => 'VARCHAR(255)',
	'shipping_costs'    => 'VARCHAR(255)',
	'coupon'            => 'VARCHAR(255)',
	'total'             => 'VARCHAR(255)',
];

// Loyalty points table
$KANSO_DEFAULTS_LOYALTY_POINTS_TABLE =
[
	'id'            => 'INTEGER(11) | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'user_id'  	    => 'INTEGER(11) | UNSIGNED',
	'description'   => 'VARCHAR(255)',
	'date'          => 'INTEGER(11) | UNSIGNED',
	'points_add'    => 'INTEGER(11) | UNSIGNED',
	'points_minus'  => 'INTEGER(11) | UNSIGNED',
];

// Loyalty coupons table
$KANSO_DEFAULTS_LOYALTY_COUPONS_TABLE =
[
	'id'            => 'INTEGER(11) | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'user_id'  	    => 'INTEGER(11) | UNSIGNED',
	'name'          => 'VARCHAR(255)',
	'description'   => 'VARCHAR(255)',
	'discount'      => 'INTEGER(11) | UNSIGNED',
	'code'          => 'VARCHAR(255)',
	'date'          => 'INTEGER(11) | UNSIGNED',
	'used'          => 'BOOLEAN | DEFAULT FALSE',
];

// Product reviews table
$KANSO_DEFAULTS_PRODUCT_REVIEWS_TABLE =
[
	'id'            => 'INTEGER(11) | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'comment_id'  	=> 'INTEGER(11) | UNSIGNED',
	'product_id'  	=> 'INTEGER(11) | UNSIGNED',
	'rating'        => 'INTEGER(11) | UNSIGNED',
	'recommended'   => 'BOOLEAN | DEFAULT FALSE',
];

// Product review votes table
$KANSO_DEFAULTS_PRODUCT_REVIEW_VOTES_TABLE =
[
	'id'          => 'INTEGER(11) | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'comment_id'  => 'INTEGER(11) | UNSIGNED',
	'up_vote'     => 'BOOLEAN | DEFAULT FALSE',
	'ip_address'  => 'VARCHAR(255)',
];

// Product review votes table
$KANSO_DEFAULTS_USED_PUBLIC_COUPONS =
[
	'id'          => 'INTEGER(11) | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
	'user_id'     => 'INTEGER(11) | UNSIGNED',
	'email'       => 'VARCHAR(255)',
	'coupon_name' => 'VARCHAR(255)',
];

// The default user entry
$KANSO_DEFAULT_USER =
[
	'username'    		 => $this->config->get('cms.default_username'),
	'email'       		 => $this->config->get('cms.default_email'),
	'hashed_pass' 		 => utf8_encode($hashed),
	'name'        		 => $this->config->get('cms.default_name'),
	'slug'        		 => 'john-appleseed',
	'facebook'    		 => 'https://www.facebook.com/example',
	'twitter'     		 => 'https://www.twitter.com/example',
	'gplus'       		 => 'https://www.plus.google.com/example',
	'instagram'       	 => 'https://www.instagram.com/example',
	'thumbnail_id'       => 1,
	'status'      		 => 'confirmed',
	'role'       		 => 'administrator',
	'description' 		 => 'This is where your author bio goes. You can put a small description about yourself here, or just leave it blank if you like.',
	'access_token'       => 'XGrcqMWdk62rjMKag6HZkueZn3K7d6PuqMramQ==',
];

// The default tags entries
$KANSO_DEFAULT_TAGS =
[
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

// The default categories entries
$KANSO_DEFAULT_CATEGORIES =
[
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

// The default articles entries
$KANSO_DEFAULT_ARTICLES =
[
	[
		'created'     => strtotime('-1 hour'),
		'modified'    => time(),
		'status'      => 'published',
		'type'        => 'post',
		'slug'        => date('Y/m/') . 'hello-world/',
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
		'slug'        => date('Y/m/') . 'markdown-basics/',
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
		'slug'        => date('Y/m/') . 'elements/',
		'title'       => 'Elements',
		'excerpt'     => 'The purpose of this HTML is to help determine what default settings are with CSS and to make sure that all possible HTML Elements are included in this HTML so as to not miss any possible Elements when designing a site.',
		'author_id'   => 1,
		'thumbnail_id'     => 4,
		'comments_enabled' => true,
	],
	[
		'created'     => strtotime('-2 months'),
		'modified'    => time(),
		'status'      => 'published',
		'type'        => 'product',
		'slug'        => 'products/html/example-product/',
		'title'       => 'Elements',
		'excerpt'     => 'This is an example product! Check it out',
		'author_id'   => 1,
		'thumbnail_id'     => 4,
		'comments_enabled' => true,
		'meta'        => [
			'offers' =>
			[
				[
					'offer_id'   => 'SKU3424',
					'name'       => 'XXS',
					'price'      => 19.95,
					'sale_price' => 9.95,
					'instock'    => true,
				],
				[
					'offer_id'   => 'SKU3436',
					'name'       => 'XXL',
					'price'      => 19.95,
					'sale_price' => 9.95,
					'instock'    => true,
				],
			],
		],
	],
];

// The default article content entries
$KANSO_DEFAULT_ARTICLE_CONTENT =
[
	file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'HelloWorld.md'),
	file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'MarkdownBasics.md'),
	file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Elements.md'),
	file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Product.md'),
];

// The default comments entries
$KANSO_DEFAULT_COMMENTS =
[
	[
		'post_id'      => 1,
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
	[
		'post_id'      => 4,
		'date'         => strtotime('-1 hour'),
		'type'         => 'comment',
		'status'       => 'approved',
		'name'     	   => 'Tim Doe',
		'email'    	   => 'TimDoe@example.com',
		'content'  	   => 'This is a product review.',
		'html_content' => '<p>This is a product review.<p>',
		'ip_address'   => '192.168.1.3',
		'email_reply'  => false,
		'email_thread' => false,
		'rating'       => 2,
	],
];

// The default media library entries
$_imgesDir   = $this->config->get('cms.uploads.path');
$_imagesUrl  = str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['HTTP_HOST'], $_imgesDir);

// localhost bugfix
if (!strpos($_imagesUrl, 'http://') !== false)
{
    $_imagesUrl = 'http://' . $_imagesUrl;
}

$KANSO_DEFAULT_IMAGES =
[
	[
		'url'  	       => $_imagesUrl . DIRECTORY_SEPARATOR . 'author_img.png',
		'path'  	   => $_imgesDir . DIRECTORY_SEPARATOR . 'author_img.png',
		'title'	       => 'Default Author Image',
		'alt'	       => 'Author\'s profile photo',
		'size'         => 443576,
		'dimensions'   => '1200 x 1200',
		'date'         => time(),
		'uploader_id'  => 1,
	],
	[
		'url'  	       => $_imagesUrl . DIRECTORY_SEPARATOR . 'hero1.jpg',
		'path'  	   => $_imgesDir . DIRECTORY_SEPARATOR . 'hero1.jpg',
		'title'	       => 'New York City',
		'alt'	       => 'Photo of New York City',
		'size'         => 222994,
		'dimensions'   => '1200 x 800',
		'date'         => time(),
		'uploader_id'  => 1,
	],
	[
		'url'  	       => $_imagesUrl . DIRECTORY_SEPARATOR . 'hero2.jpg',
		'path'  	   => $_imgesDir . DIRECTORY_SEPARATOR . 'hero2.jpg',
		'title'	       => 'Beautiful Landscape with Lake',
		'alt'	       => 'Landscape photo of lake and mountains',
		'size'         =>  140531,
		'dimensions'   => '1200 x 595',
		'date'         => time(),
		'uploader_id'  => 1,
	],
	[
		'url'  	       => $_imagesUrl . DIRECTORY_SEPARATOR . 'hero3.jpg',
		'path'  	   => $_imgesDir . DIRECTORY_SEPARATOR . 'hero3.jpg',
		'title'	       => 'Beautiful Autumn Leaves',
		'alt'	       => 'Photo of track with autumn leaves',
		'size'         => 222897,
		'dimensions'   => '1200 x 701',
		'date'         => time(),
		'uploader_id'  => 1,
	],
];
