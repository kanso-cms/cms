<?php

namespace Kanso\Storage\Cookie;

use Kanso\Storage\Cookie\OpenSSL;
use Kanso\Storage\Cookie\Signer;

class Cookie {

    # Default options
    private $defaults = [
        'secret'   => '`F0=nYsPkxolnlyc+z6jcnRdulJEOfqIyMWwlxeYtnFPi[lKMb',
        'cipher'   => 'AES-256-OFB',
        'domain'   => 'localhost',
        'expire'   => '2147368447',
        'secure'   => false,
        'path'     => '/',
    ];

    # Options
    private $options = [];

    private $openSSL;

    private $signer;

    private $default_memory;

    public function __construct($options = [])
    {
        $this->options = array_merge($this->defaults, $options);
        $this->openSSL = new OpenSSL($this->options['secret'], $this->options['cipher']);
        $this->signer  = new Signer($this->options['secret']);
        $this->default_memory = ini_get('memory_limit');
    }

    public function store($name, $str)
    {
        $this->boostMemory();
        $data = $this->signer->sign($this->openSSL->encrypt($str));
        $this->restoreMemory();
        return [$name, $data, $this->options['expire'], $this->options['path'], $this->options['domain'], $this->options['secure']];
    }
    
    public function fetch($name)
    {
        if (!isset($_COOKIE[$name])) return false;
        return $this->unstore($_COOKIE[$name]);
    }
    
    private function unstore($str)
    {
        $this->boostMemory();
        $unsigned = $this->signer->validate($str);
        if (!$unsigned) return false;
        $decrypt = $this->openSSL->decrypt($unsigned);
        $this->restoreMemory();
        return $decrypt;
    }

    private function boostMemory()
    {
        ini_set('memory_limit', '1024M');
    }

    private function restoreMemory()
    {
        ini_set('memory_limit', $this->default_memory);
    }
  
}