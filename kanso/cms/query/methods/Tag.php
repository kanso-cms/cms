<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

/**
 * CMS Query tag methods.
 *
 * @author Joe J. Howard
 */
trait Tag
{
    /**
     * Checks whether a given tag exists by the tag name or id.
     *
     * @access  public
     * @param  string|int $tag_name Tag name or id
     * @return bool
     */
    public function tag_exists($tag_name)
    {
        $index = is_numeric($tag_name) ? 'id' : 'name';

        $tag_name = is_numeric($tag_name) ? intval($tag_name) : $tag_name;

        return !empty($this->TagManager->provider()->byKey($index, $tag_name));
    }

    /**
     * Gets an array of tag objects of the current post or a post by id.
     *
     * @access  public
     * @param  int                          $post_id Post id or null for tags of current post (optional) (Default NULL)
     * @return \kanso\cms\wrappers\Tag|null
     */
    public function the_tags(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            if ($post)
            {
                return $post->tags;
            }
        }
        elseif (!empty($this->post))
        {
            return $this->post->tags;
        }

        return [];
    }

    /**
     * Get a comma separated list of the tag names of the current post or a post by id.
     *
     * @access public
     * @param  int    $post_id Post id or null for tags of current post (optional) (Default NULL)
     * @param  string $glue    Glue to separate tag names
     * @return string
     */
    public function the_tags_list(int $post_id = null, string $glue = ', '): string
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);

            if ($post)
            {
                return $this->listTags($post->tags, $glue);
            }
        }
        elseif (!empty($this->post))
        {
            return $this->listTags($this->post->tags, $glue);
        }

        return '';
    }

    /**
     * Implode tag names.
     *
     * @access private
     * @param  array  $categories Array of tag objects
     * @param  string $glue       Glue to separate tag names
     * @return string
     */
    private function listTags(array $tags, string $glue): string
    {
        $str = '';

        foreach ($tags as $tag)
        {
            $str .= $tag->name . $glue;
        }

        $split = array_filter(explode($glue, $str));

        return implode($glue, $split);
    }

    /**
     * Get the slug of a tag by id or the current post's tag.
     *
     * @access  public
     * @param  int         $tag_id Tag id or null for tag of current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_tag_slug(int $tag_id = null)
    {
        $tag = false;

        if (!$tag_id)
        {
            if (!empty($this->post))
            {
                $tag = $this->post->tags[0];
            }
        }
        else
        {
            $tag = $this->getTagById($tag_id);
        }

        if ($tag)
        {
            return $tag->slug;
        }

        return false;
    }

    /**
     * Get the full URL of a tag by id or current post's tag.
     *
     * @access  public
     * @param  int         $tag_id Tag id or null for tag of current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_tag_url(int $tag_id = null)
    {
        $tag = false;

        if (!$tag_id)
        {
            if (!empty($this->post))
            {
                $tag = $this->post->tags[0];
            }
        }
        else
        {
            $tag = $this->getTagById($tag_id);
        }

        if ($tag)
        {
            $prefix = !empty($this->blog_location()) ? '/' . $this->blog_location() . '/' : '/';

            return $this->Request->environment()->HTTP_HOST . $prefix . 'tag/' . $tag->slug . '/';
        }

        return false;
    }

    /**
     * If the request is for a tag, category or author returns the object of that request.
     *
     * @access public
     * @return mixed
     */
    public function the_taxonomy()
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        if ($this->requestType === 'category')
        {
            return $this->cache->set($key, $this->CategoryManager->provider()->byKey('slug', $this->taxonomySlug, true));
        }
        elseif ($this->requestType === 'tag')
        {
            return $this->cache->set($key, $this->TagManager->provider()->byKey('slug', $this->taxonomySlug, true));
        }
        elseif ($this->requestType === 'author')
        {
            return $this->cache->set($key, $this->UserManager->provider()->byKey('slug', $this->taxonomySlug, true));
        }

        return null;
    }

    /**
     * Get an array of all the tag objects.
     *
     * @access  public
     * @return array
     */
    public function all_the_tags(): array
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        $tags = [];

        $rows = $this->SQL->SELECT('id')->FROM('tags')->FIND_ALL();

        foreach ($rows as $row)
        {
            $tags[] = $this->TagManager->byId($row['id']);
        }

        return $this->cache->set($key, $tags);
    }

    /**
     * Is the current post or a post by id untagged ?
     *
     * @access  public
     * @param  int  $post_id Post id or null for tag of current post (optional) (Default NULL)
     * @return bool
     */
    public function has_tags(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);

            if ($post)
            {
                $tags = $post->tags;

                if (count($tags) === 1)
                {
                    if ($tags[0]->id === 1) return false;
                }

                return true;
            }

            return false;
        }

        if (!empty($this->post))
        {
            $tags = $this->post->tags;

            if (count($tags) === 1)
            {
                if ($tags[0]->id === 1) return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Ge an array of Post objects by tag id.
     *
     * @param  int   $tag_id    The Tag id
     * @param  bool  $published Get only published articles (optional) (Default TRUE)
     * @return array
     */
    public function the_tag_posts(int $tag_id, bool $published = true): array
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        if ($this->tag_exists($tag_id))
        {
            return $this->cache->set($key, $this->PostManager->provider()->byKey('tags.id', $tag_id, false, $published));
        }

        return $this->cache->set($key, []);
    }
}
