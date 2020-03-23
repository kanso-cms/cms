<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema;

use kanso\cms\schema\json\Address;
use kanso\cms\schema\json\Article;
use kanso\cms\schema\json\Author;
use kanso\cms\schema\json\Brand;
use kanso\cms\schema\json\Breadcrumb;
use kanso\cms\schema\json\Image;
use kanso\cms\schema\json\ItemList;
use kanso\cms\schema\json\JsonInterface;
use kanso\cms\schema\json\Logo;
use kanso\cms\schema\json\Organization;
use kanso\cms\schema\json\Product;
use kanso\cms\schema\json\Profile;
use kanso\cms\schema\json\Search;
use kanso\cms\schema\json\Webpage;
use kanso\cms\schema\json\Website;
use kanso\framework\mvc\model\Model;

/**
 * SEO Schema.
 *
 * @author Joe J. Howard
 */
class Schema extends Model
{
    /**
     * JSON Generators.
     *
     * @var array
     */
    private $generators =
    [
        Organization::class,
        Brand::class,
        Website::class,
        Webpage::class,
        Product::class,
        Profile::class,
        Article::class,
        Author::class,
        Address::class,
        Logo::class,
        Search::class,
        Image::class,
        ItemList::class,
        Breadcrumb::class,
    ];

    /**
     * The schema graph.
     *
     * @var bool
     */
    private $haveBreadcrumb = false;

    /**
     * The schema graph.
     *
     * @var array
     */
    private $graph = [];

    /**
     * Create and return the schema.org graph.
     *
     * @return array
     */
    public function graph(): array
    {
        $schema = [];

        foreach ($this->generators as $class)
        {
            $this->graph[strval($class)] = $this->invokeSchemaClass($class)->generate();
        }

        $schema =
        [
            '@context' => 'https://schema.org',
            '@graph'   => $this->sanitizeGraph(),
        ];

        return $schema;
    }

    /**
     * Invoke and return schema component by classname.
     *
     * @param  string                                        $className The classname to invoke
     * @return \kanso\cms\schema\json\JsonInterface
     */
    private function invokeSchemaClass(string $className): JsonInterface
    {
        return new $className($this->Request, $this->Response, $this->Config, $this->Ecommerce, $this->PostManager, $this->ProductManager, $this->BundleManager, $this->CategoryManager, $this->TagManager, $this->UserManager, $this->MediaManager, $this->CommentManager);
    }

    /**
     * Sanitize the final output.
     *
     * @return array
     */
    private function sanitizeGraph(): array
    {
        $this->haveBreadcrumb = !empty($this->graph[strval(Breadcrumb::class)]);

        foreach ($this->graph as $class => $data)
        {
            foreach ($data as $key => $val)
            {
                if ($key === 'breadcrumb' && !$this->haveBreadcrumb)
                {
                    unset($this->graph[$class][$key]);
                }
            }
        }

        return array_values(array_filter($this->graph));
    }

}
