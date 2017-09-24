<?php

return
[
	/**
	 * ---------------------------------------------------------
	 * CMS email theme
	 * ---------------------------------------------------------
	 *
	 * These settings setup the styling theme for emails sent by 
	 * the CMS. They are pretty much self explanatory.
	 */
	'theme' => 
	[
        'body_bg'         => '#FFFFFF',
        'content_bg'      => '#FFFFFF',
        'content_border'  => '1px solid #DADADA',
        'font_family'     => '"Helvetica Neue", Helvetica, Arial, sans-serif',
        'font_size'       => '13.5px',
        'line_height'     => '27px',
        'body_color'      => '#4e5358',
        'link_color'      => '#62c4b6',
        'color_gray'      => '#c7c7c7',
        'color_gray_dark' => '#b1b1b1',
        'btn_bg'          => '#62c4b6',
        'btn_color'       => '#ffffff',
        'btn_hover_bg'    => '#48ad9e',
        'btn_size'        => '18px',
        'btn_font_size'   => '13px',
        'border_radius'   => '3px',
        'logo_link'       => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' ? 'https://'.$_SERVER['HTTP_HOST'] : 'http://'.$_SERVER['HTTP_HOST'],
        'logo_url'        => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' ? 'https://'.$_SERVER['HTTP_HOST'].'/app/public/uploads/kanso-logo.jpg' : 'http://'.$_SERVER['HTTP_HOST'].'/app/public/uploads/kanso-logo.jpg',
        'font_size_h1'    => '30px',
        'font_size_h2'    => '28px',
        'font_size_h3'    => '24px',
        'font_size_h4'    => '20px',
        'font_size_h5'    => '18px',
        'font_size_h6'    => '15px',
    ],

    /**
     * ---------------------------------------------------------
     * CMS email paths
     * ---------------------------------------------------------
     *
     * These are the slugs for when you use the Gatekeeper functions 
     * to register new users, forgot password, reset password etc..
     * These are for your own application purpose and have no effect
     * on the admin panel
     * 
     * login            : Slug to use in emails for links to your application's login url
     * register         : Slug to use in emails for links to your application's register url
     * forgot_password  : Slug to use in emails for links to your application's forgot password url
     * reset_password   : Slug to use in emails for links to your application's reset password url
     * reset_password   : Slug to use in emails for links to your application's confirm account url
     */
    'urls' =>
    [

        'login'           => 'login/',
        'register'        => 'register/',
        'forgot_password' => 'forgot-password/',
        'reset_password'  => 'reset-password/',
        'confirm_account' => 'confirm-account/'
    ],
];
