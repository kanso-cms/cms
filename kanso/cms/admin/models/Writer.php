<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\utility\Str;

/**
 * Comments model.
 *
 * @author Joe J. Howard
 */
class Writer extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn())
        {
            return $this->parseGet();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new InvalidTokenException('Bad Admin Panel POST Request. The CSRF token was either not provided or was invalid.');
        }

        if (isset($this->post['ajax_request']))
        {
            $request = $this->post['ajax_request'];

            if ($request === 'writer_publish_article')
            {
                return $this->publishArticle();
            }
            elseif ($request === 'writer_save_existing_article')
            {
                return $this->saveExistingArticle();
            }
            elseif ($request === 'writer_save_new_article')
            {
                return $this->saveNewArticle();
            }
        }

        throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
    }

    /**
     * Parse the $_GET request variables and filter the articles for the requested page.
     *
     * @return array
     */
    private function parseGet(): array
    {
        $queries   = $this->Request->queries();
        $post      = false;

        if (isset($queries['id']))
        {
            $post = $this->PostManager->byId(intval($queries['id']));
        }

        return ['the_post' => $post];
    }

    /**
     * Publish an existing or new article.
     *
     * @return array
     */
    private function publishArticle()
    {
        $this->post['status'] = 'published';

        if (isset($this->post['id']) && !empty($this->post['id']) && is_numeric($this->post['id']))
        {
            return $this->saveExistingArticle();
        }
        else
        {
            return $this->saveNewArticle();
        }
    }

    /**
     * Save an existing article.
     *
     * @return array|false
     */
    private function saveExistingArticle()
    {
        $rules =
        [
            'type'  => ['required'],
            'id'    => ['required', 'integer'],
        ];
        $filters =
        [
            'title'        => ['trim', 'string'],
            'category'     => ['trim', 'string'],
            'tags'         => ['trim', 'string'],
            'type'         => ['trim', 'string'],
            'excerpt'      => ['trim', 'string'],
            'status'       => ['trim', 'string'],
            'comments'     => ['trim', 'boolean'],
            'id'           => ['trim', 'integer'],
            'thumbnail_id' => ['trim', 'integer'],
            'author'       => ['trim', 'integer'],
        ];

        $validator = $this->container->Validator->create($this->post, $rules, $filters);

        if (!$validator->isValid())
        {
            return false;
        }

        $post = $validator->filter();

        $article = $this->PostManager->byId($post['id']);

        $postMeta = $this->getPostMeta();

        if (!$article)
        {
            return false;
        }

        $article->title            = $post['title'];
        $article->categories       = $post['category'];
        $article->tags             = $post['tags'];
        $article->excerpt          = $post['excerpt'];
        $article->type             = $post['type'];
        $article->author_id        = $post['author'];
        $article->comments_enabled = $post['comments'];
        $article->thumbnail_id     = $post['thumbnail_id'];
        $article->meta             = !empty($postMeta) ? $postMeta : null;

        if (isset($_POST['content']))
        {
            $article->content = $_POST['content'];
        }

        if (isset($post['status']))
        {
            $article->status = $post['status'];
        }

        if (empty($post['excerpt']))
        {
            $article->excerpt = $_POST['content'];
        }
        else
        {
            if (empty($article->excerpt))
            {
                $article->excerpt = $post['excerpt'];
            }
            else
            {
                $article->excerpt = $article->excerpt;
            }
        }

        if ($article->save())
        {
            // Clear from cache
            if ($this->Config->get('cache.http_cache_enabled') === true)
            {
                $key = $this->Config->get('cms.blog_location') . '/' . $this->Query->the_slug($article->id);
                $key = Str::alphaDash($key);
                $this->Cache->delete($key);
            }

            // Return slug/id
            $suffix = $article->status === 'published' ? '' : '?draft';

            if ($post['type'] === 'post')
            {
                $blogPrefix = $this->Config->get('cms.blog_location');

                return ['id' => $article->id, 'slug' => !$blogPrefix ? $article->slug . $suffix : $blogPrefix . '/' . $article->slug . $suffix];
            }

            return ['id' => $article->id, 'slug' => $article->slug . $suffix];
        }

        return false;
    }

    /**
     * Save a new article.
     *
     * @return array|false
     */
    private function saveNewArticle()
    {
        // Sanitize and validate the POST variables
        $rules =
        [
            'type'  => ['required'],
        ];
        $filters =
        [
            'title'        => ['trim', 'string'],
            'category'     => ['trim', 'string'],
            'tags'         => ['trim', 'string'],
            'type'         => ['trim', 'string'],
            'excerpt'      => ['trim', 'string'],
            'status'       => ['trim', 'string'],
            'comments'     => ['trim', 'boolean'],
            'thumbnail_id' => ['trim', 'integer'],
            'author'       => ['trim', 'integer'],
        ];

        $validator = $this->container->Validator->create($this->post, $rules, $filters);

        if (!$validator->isValid())
        {
            return false;
        }

        $post = $validator->filter();

        // Get the article content directly from the _POST global
        // so it is not filtered in any way
        $post['content'] = !empty($post['content']) ? $_POST['content'] : '';

        // Default is to save as draft
        $post['status'] = !isset($post['status']) ? 'draft' : $post['status'];

        // Default excerpt
        $post['excerpt'] = empty($post['excerpt']) ? $_POST['content'] : $post['excerpt'];

        $postMeta = $this->getPostMeta();

        $newPost = $this->PostManager->create([
            'title'            => $post['title'],
            'categories'       => $post['category'],
            'tags'             => $post['tags'],
            'excerpt'          => $post['excerpt'],
            'thumbnail_id'     => $post['thumbnail_id'],
            'status'           => $post['status'],
            'type'             => $post['type'],
            'author_id'        => $post['author'],
            'content'          => $post['content'],
            'comments_enabled' => $post['comments'],
            'meta'             => !empty($postMeta) ? serialize($postMeta) : null,
        ]);

        if ($newPost)
        {
            $suffix = $post['status'] === 'published' ? '' : '?draft';

            if ($newPost->type === 'post')
            {
                $blogPrefix = $this->Config->get('cms.blog_location');

                return ['id' => $newPost->id, 'slug' => !$blogPrefix ? $newPost->slug . $suffix : $blogPrefix . '/' . $newPost->slug . $suffix];
            }

            return ['id' => $newPost->id, 'slug' => $newPost->slug . $suffix];
        }

        return false;
    }

    /**
     * Sorts and organises the post meta.
     *
     * @return array
     */
    private function getPostMeta(): array
    {
        $keys     = [];
        $values   = [];
        $response = [];

        if (isset($_POST['meta_title']))
        {
            $title = strip_tags(trim($_POST['meta_title']));

            if ($title !== '')
            {
                $response['meta_title'] = $title;
            }
        }
        if (isset($_POST['meta_description']))
        {
            $desc = strip_tags(trim($_POST['meta_description']));

            if ($desc !== '')
            {
                $response['meta_description'] = $desc;
            }
        }        

        if (isset($_POST['post-meta-keys']))
        {
            $keys = json_decode($_POST['post-meta-keys'], true);
        }
        if (isset($_POST['post-meta-values']))
        {
            $values = json_decode($_POST['post-meta-values'], true);
        }

        if (is_array($values) && is_array($keys) && count($values) === count($keys))
        {
            foreach ($keys as $i => $k)
            {
                $response[trim($k, '\'')] = trim($values[$i], '\'');
            }
        }

        $offers = [];

        $offerKeys =
        [
            'product_offer_X_id'         => 'offer_id',
            'product_offer_X_name'       => 'name',
            'product_offer_X_price'      => 'price',
            'product_offer_X_sale_price' => 'sale_price',
            'product_offer_X_instock'    => 'instock',
        ];

        for ($i=1; $i <= 20; $i++)
        {
            $offer = [];

            foreach ($offerKeys as $postKey => $offerKey)
            {
                $postKey = str_replace('X', strval($i), $postKey);

                if (isset($_POST[$postKey]))
                {
                    $offer[$offerKey] = $offerKey === 'instock' ? Str::bool($_POST[$postKey]) : trim($_POST[$postKey]);

                    if ($offerKey === 'sale_price' || $offerKey === 'price')
                    {
                        $offer[$offerKey] = floatval($_POST[$postKey]);
                    }
                }
            }

            if (!empty($offer))
            {
                $offers[] = $offer;
            }
        }

        if (!empty($offers))
        {
            $response['offers'] = $offers;
        }

        return $response;
    }
}
