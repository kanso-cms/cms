<?php

namespace Kanso\CDN;
/**
 * grunt-cdnify
 * https://github.com/callumlocke/grunt-cdnify
 *
 * Copyright 2014 Callum Locke
 * Licensed under the MIT license.
 */

class CdnFilter 
{
	private $cdnUrl;

	private $baseUrl;

	private $htmlStr;

	private $selectors = [

	];

	public function __construct($baseUrl, $cdnUrl, $htmlStr)
    {
    	$this->baseUrl = rtrim($baseUrl, '/');

    	$this->cdnUrl  = rtrim($cdnUrl, '/');

    	$this->htmlStr = $htmlStr;
    }

    public function filter()
    {

        # Store variables locally
        $baseUrl = $this->baseUrl;
    	$cdnUrl  = $this->cdnUrl;
    	$htmlStr = $this->htmlStr;

        # Replace img tags
        $htmlStr = preg_replace_callback('/<img [^>]*src="([^"]+)"[^>]*/', function($matches) use ($baseUrl, $cdnUrl) {
            if (strpos($matches[0], $baseUrl) !== false) return str_replace($baseUrl, $cdnUrl, $matches[0]);
            return $matches[0];
        }, $htmlStr);

        # Replace favicions and style sheets
        $htmlStr = preg_replace_callback('/<link rel="(shortcut icon|icon|stylesheet|apple-touch-icon)" href="([^"]+)"/', function($matches) use ($baseUrl, $cdnUrl) {
            if (strpos($matches[0], $baseUrl) !== false) return str_replace($baseUrl, $cdnUrl, $matches[0]);
            return $matches[0];
        }, $htmlStr);

        $htmlStr = preg_replace_callback('/<link href="([^"]+)".+rel="(shortcut icon|icon|stylesheet|apple-touch-icon)"/', function($matches) use ($baseUrl, $cdnUrl) {
            if (strpos($matches[0], $baseUrl) !== false) return str_replace($baseUrl, $cdnUrl, $matches[0]);
            return $matches[0];
        }, $htmlStr);

        # Replace scripts
        $htmlStr = preg_replace_callback('/<script.+src="([^"]+)"/', function($matches) use ($baseUrl, $cdnUrl) {
            if (strpos($matches[0], $baseUrl) !== false) return str_replace($baseUrl, $cdnUrl, $matches[0]);
            return $matches[0];
        }, $htmlStr);
        
        # Background urls
		$htmlStr = preg_replace_callback('/background:url\(([^)]+)\)/', function($matches) use ($baseUrl, $cdnUrl) {
            if (strpos($matches[0], $baseUrl) !== false) return str_replace($baseUrl, $cdnUrl, $matches[0]);
            return $matches[0];
        }, $htmlStr);

		return $htmlStr;
    }
}