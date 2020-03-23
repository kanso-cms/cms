<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

use kanso\cms\ecommerce\Ecommerce;
use kanso\cms\wrappers\managers\CategoryManager;
use kanso\cms\wrappers\managers\CommentManager;
use kanso\cms\wrappers\managers\MediaManager;
use kanso\cms\wrappers\managers\PostManager;
use kanso\cms\ecommerce\managers\ProductManager;
use kanso\cms\ecommerce\managers\BundleManager;
use kanso\cms\wrappers\managers\TagManager;
use kanso\cms\wrappers\managers\UserManager;
use kanso\framework\config\Config;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;

/**
 * JSON generator base class.
 *
 * @author Joe J. Howard
 */
abstract class JsonGenerator
{
    /**
     * Request instance.
     *
     * @var \kanso\framework\http\request\Request
     */
    protected $Request;

    /**
     * Response instance.
     *
     * @var \kanso\framework\http\response\Response
     */
    protected $Response;

    /**
     * Config instance.
     *
     * @var \kanso\framework\config\Config
     */
    protected $Config;

    /**
     * Ecommerce instance.
     *
     * @var \kanso\cms\ecommerce\Ecommerce
     */
    protected $Ecommerce;

    /**
     * PostManager instance.
     *
     * @var \kanso\cms\wrappers\managers\PostManager
     */
    protected $PostManager;

    /**
     * ProductManager instance.
     *
     * @var \kanso\cms\ecommerce\managers\ProductManager
     */
    protected $ProductManager;

    /**
     * BundleManager instance.
     *
     * @var \kanso\cms\ecommerce\managers\BundleManager
     */
    protected $BundleManager;

    /**
     * CategoryManager instance.
     *
     * @var \kanso\cms\wrappers\managers\CategoryManager
     */
    protected $CategoryManager;

    /**
     * TagManager instance.
     *
     * @var \kanso\cms\wrappers\managers\TagManager
     */
    protected $TagManager;

    /**
     * UserManager instance.
     *
     * @var \kanso\cms\wrappers\managers\UserManager
     */
    protected $UserManager;

    /**
     * MediaManager instance.
     *
     * @var \kanso\cms\wrappers\managers\MediaManager
     */
    protected $MediaManager;

    /**
     * CommentManager instance.
     *
     * @var \kanso\cms\wrappers\managers\CommentManager
     */
    protected $CommentManager;

    /**
     * Constructor.
     *
     * @param \kanso\framework\http\request\Request        $request         Request instance
     * @param \kanso\framework\http\response\Response      $response        Response instance
     * @param \kanso\framework\config\Config               $config          Config instance
     * @param \kanso\cms\ecommerce\Ecommerce               $ecommerce       Ecommerce instance
     * @param \kanso\cms\wrappers\managers\PostManager     $postmanager     PostManager instance
     * @param \kanso\cms\ecommerce\managers\ProductManager $productmanager  ProductManager instance
     * @param \kanso\cms\ecommerce\managers\BundleManager  $bundlemanager   BundleManager instance
     * @param \kanso\cms\wrappers\managers\CategoryManager $categorymanager CategoryManager instance
     * @param \kanso\cms\wrappers\managers\TagManager      $tagmanager      TagManager instance
     * @param \kanso\cms\wrappers\managers\UserManager     $usermanager     UserManager instance
     * @param \kanso\cms\wrappers\managers\MediaManager    $mediamanager    MediaManager instance
     * @param \kanso\cms\wrappers\managers\CommentManager  $commentmanager  commentmanager instance
     */
    public function __construct(Request $request, Response $response, Config $config, Ecommerce $ecommerce, PostManager $postmanager, ProductManager $productmanager, BundleManager $bundlemanager, CategoryManager $categorymanager, TagManager $tagmanager, UserManager $usermanager, MediaManager $mediamanager, CommentManager $commentmanager)
    {
        $this->Request         = $request;
        $this->Response        = $response;
        $this->Config          = $config;
        $this->Ecommerce       = $ecommerce;
        $this->PostManager     = $postmanager;
        $this->ProductManager  = $productmanager;
        $this->BundleManager   = $bundlemanager;
        $this->CategoryManager = $categorymanager;
        $this->TagManager      = $tagmanager;
        $this->UserManager     = $usermanager;
        $this->MediaManager    = $mediamanager;
        $this->CommentManager  = $commentmanager;
    }
}
