<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\cms\admin\models\BaseModel;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;

/**
 * Settings model
 *
 * @author Joe J. Howard
 */
class Settings extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn)
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
        if ($this->isLoggedIn)
        {
            if ($this->Response->session()->token()->verify($this->post['access_token']))
            {
                return $this->parsePost();
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        return false;
    }

    /**
     * Parse the $_GET request variables and filter the articles for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        $_themes = array_filter(glob($this->Config->get('cms.themes_path').'/*'), 'is_dir');
        
        $themes  = [];
        
        foreach ($_themes as $i => $_theme)
        {
            $themes[] = substr($_theme, strrpos($_theme, '/') + 1);
        }

        $activeTab = strtolower(str_replace('settings', '', $this->requestName));

        return [
            'active_tab'  => !$activeTab ? 'account' : $activeTab,
            'themes'      => $themes,
        ];
    }

    /**
     * Parse and validate the POST request from any submitted forms
     * 
     * @access private
     * @return array|false
     */
    public function parsePost()
    {
        if (isset($this->post['form_name']))
        {
            $formName = $this->post['form_name'];

            if ($formName === 'account_settings')
            {
                return $this->submitAccountSettings();
            }
            else if ($formName === 'author_settings')
            {
                return $this->submitAuthorSettings();
            }
            else if ($formName === 'kanso_settings')
            {
                return $this->submitKansoSettings();
            }
            else if ($formName === 'restore_kanso')
            {
                return $this->submitRestoreKanso();
            }
            else if ($formName === 'invite_user')
            {
                return $this->submitInviteUser();
            }
            else if ($formName === 'change_user_role')
            {
                return $this->submitChangeUserRole();
            }
            else if ($formName === 'delete_user')
            {
                return $this->submitDeleteUser();
            }
        }
    }

    /**
     * Parse, validate and process the account settings form
     * 
     * @access private
     * @return array|false
     */
    private function submitAccountSettings()
    {
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'username'            => 'required|alpha_dash|max_len,100|min_len,4',
            'email'               => 'required|valid_email',
            'password'            => 'max_len,100|min_len,6',
            'email_notifications' => 'boolean',
        ]);

        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
            'email'    => 'trim|sanitize_email',
            'password' => 'trim',
            'email_notifications' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($post);

        if (!$validated_data)
        {
            return false;
        }

        $username = $validated_data['username'];
        $email    = $validated_data['email'];
        $password = $validated_data['password'];
        $emailNotifications = isset($validated_data['email_notifications']) ? true : false;

        # Grab the user's object
        $user = $this->Gatekeeper->getUser();

        # Validate that the username/ email doesn't exist already
        # only if the user has changed either value
        if ($email !== $user->email)
        {
            if ($this->UserManager->byEmail($email))
            {
                return $this->postMessage('warning', 'Another user already exists with that email. Please try another email address.');
            }
        }
        if ($username !== $user->username)
        {
            if ($this->UserManager->byUsername($username))
            {
                return $this->postMessage('warning', 'Another user already exists with that username. Please try another username.');
            }
        }

        # Update the user
        $user->username = $username;
        $user->email    = $email;
        $user->email_notifications = $emailNotifications;

        # If they changed their password lets update it
        if ($password !== '' && !empty($password))
        {
            $user->hashed_pass = utf8_encode($this->Crypto->password()->hash($password));
        }

        $user->save();

        $this->Gatekeeper->refreshUser();
      
        return $this->postMessage('success', 'Your account settings were successfully updated!');
    }

    /**
     * Parse, validate and process the author settings form
     *
     * @access private
     * @return array|false
     */
    private function submitAuthorSettings()
    {

        # Sanitize and validate the POST
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'name'         => 'required|alpha_space|max_len,50|min_len,3',
            'slug'         => 'required|alpha_dash|max_len,50|min_len,3',
            'description'  => 'max_len,255',
            'facebook'     => 'valid_url',
            'twitter'      => 'valid_url',
            'gplus'        => 'valid_url',
            'instagram'    => 'valid_url',
        ]);

        $this->validation->filter_rules([
            'name'         => 'trim|sanitize_string',
            'slug'         => 'trim|sanitize_string',
            'description'  => 'trim|sanitize_string',
            'facebook'     => 'trim|sanitize_string',
            'twitter'      => 'trim|sanitize_string',
            'gplus'        => 'trim|sanitize_string',
            'instagram'    => 'trim|sanitize_string',
            'thumbnail_id' => 'trim|sanitize_numbers',
        ]);

        # Validate POST
        $validated_data = $this->validation->run($post);
        
        if (!$validated_data)
        {
            return false;
        }

        # Grab the Row and update settings
        $user = $this->Gatekeeper->getUser();

        # Change authors details
        $user->name         = $validated_data['name'];
        $user->slug         = $validated_data['slug'];
        $user->facebook     = $validated_data['facebook'];
        $user->twitter      = $validated_data['twitter'];
        $user->gplus        = $validated_data['gplus'];
        $user->instagram    = $validated_data['instagram'];
        $user->description  = $validated_data['description'];
        $user->thumbnail_id = empty($validated_data['thumbnail_id']) ? null : intval($validated_data['thumbnail_id']);
        $user->save();

        $this->Gatekeeper->refreshUser();

        return $this->postMessage('success', 'Your author information was successfully updated!');
    }

    /**
     * Parse and validate the Kanso settings from the POST request
     * 
     * @param  $postVars    $_POST
     * @return array|false
     */
    private function submitKansoSettings()
    {
        # Validate the user is an admin
        if ($this->Gatekeeper->getUser()->role !== 'administrator')
        {
            return false;
        }

        # Validate post variables
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'enable_authors '   => 'boolean',
            'enable_cats'       => 'boolean',
            'enable_tags'       => 'boolean',
            'enable_cdn'        => 'boolean',
            'enable_cache'      => 'boolean',
            'enable_comments'   => 'boolean',
            'posts_per_page'    => 'required|integer',
            'thumbnail_quality' => 'required|integer',
            'cdn_url'           => 'max_len,100',
            'cache_life'        => 'max_len,50',
            'site_title'        => 'required|max_len,100',
            'site_description'  => 'required|max_len,300',
            'sitemap_url'       => 'required|max_len,100',
            'theme'             => 'required|max_len,100',
            'permalinks'        => 'required|max_len,50',
        ]);

        $this->validation->filter_rules([
            'posts_per_page'    => 'sanitize_numbers',
            'thumbnail_quality' => 'sanitize_numbers',
            'cdn_url'           => 'trim|sanitize_string|basic_tags',
            'cache_life'        => 'trim|sanitize_string|basic_tags',
            'site_title'        => 'trim|sanitize_string|basic_tags',
            'site_description'  => 'trim|sanitize_string|basic_tags',
            'sitemap_url'       => 'trim|sanitize_string|basic_tags',
            'theme'             => 'trim|sanitize_string|basic_tags',
            'permalinks'        => 'trim|sanitize_string|basic_tags',
        ]);

        if (isset($post['clear_cache']))
        {
            $this->Cache->clear();

            return $this->postMessage('success', 'The application cache was successfully cleared.');
        }

        $validated_data = $this->validation->run($post);

        if ($validated_data)
        {
            # Filter basic booleans
            $validated_data['enable_authors']  = !isset($validated_data['enable_authors'])  ? false : Str::bool($validated_data['enable_authors']);
            $validated_data['enable_cats']     = !isset($validated_data['enable_cats'])     ? false : Str::bool($validated_data['enable_cats']);
            $validated_data['enable_tags']     = !isset($validated_data['enable_tags'])     ? false : Str::bool($validated_data['enable_tags']);
            $validated_data['enable_cdn']      = !isset($validated_data['enable_cdn'])      ? false : Str::bool($validated_data['enable_cdn']);
            $validated_data['enable_cache']    = !isset($validated_data['enable_cache'])    ? false : Str::bool($validated_data['enable_cache']);
            $validated_data['enable_comments'] = !isset($validated_data['enable_comments']) ? false : Str::bool($validated_data['enable_comments']);
            $validated_data['thumbnail_quality'] = intval($validated_data['thumbnail_quality']);


            # Validate the permalinks
            if (!$this->validatePermalinks($validated_data['permalinks']))
            {
                return $this->postMessage('warning', 'The permalinks value you entered is invalid. Please ensure you enter a valid permalink structure - e.g. "year/month/postname/".');
            }

            # Validate cache life
            if ($validated_data['enable_cache'] && !$this->validateCacheLife($validated_data['cache_life']))
            {
                return $this->postMessage('warning', 'The cache life value you entered is invalid. Please ensure you enter a cache lifetime - e.g. "1 month" or "3 days".');

            }

            # Validate thumbnail quality
            if ($validated_data['thumbnail_quality'] > 100 || $validated_data['thumbnail_quality'] < 1)
            {
                return $this->postMessage('warning', 'The image quality value you entered is invalid. Please enter a number between 0 and 100.');
            }

            # Validate the CDN URL
            if ($validated_data['enable_cdn'] && !filter_var($validated_data['cdn_url'], FILTER_VALIDATE_URL))
            {
                return $this->postMessage('warning', 'The CDN URL you entered is invalid. Please provide a valid URL.');
            }

            # Filter the cache life
            $validated_data['cache_life'] = $this->filterCacheLife($validated_data['cache_life']);

            # Filter the permalinks
            $permalinks = $this->filterPermalinks($validated_data['permalinks']);

            # Previous permalinks value
            $oldPermalinks = $this->Config->get('cms.permalinks');

            $cms =
            [
                "theme_name"       => $validated_data['theme'],
                "site_title"       => $validated_data['site_title'],
                "site_description" => $validated_data['site_description'], 
                "sitemap_route"    => $validated_data['sitemap_url'],
                "permalinks"       => $permalinks['permalinks'],
                "permalinks_route" => $permalinks['permalinks_route'],
                "posts_per_page"   => $validated_data['posts_per_page'] < 1 ? 10 : intval($validated_data['posts_per_page']),
                "route_tags"       => Str::bool($validated_data['enable_tags']),
                "route_categories" => Str::bool($validated_data['enable_cats']),
                "route_authors"    => Str::bool($validated_data['enable_authors']),
                "enable_comments"  => Str::bool($validated_data['enable_comments']),
            ];

            foreach ($cms as $key => $val)
            {
                $this->Config->set('cms.'.$key, $val);
            }

            $this->Config->set('cms.uploads.thumbnail_quality', $validated_data['thumbnail_quality']);

            $this->Config->set('cdn.enabled', $validated_data['enable_cdn']);
            $this->Config->set('cdn.host', $validated_data['cdn_url']);

            $this->Config->set('cache.http_cache_enabled', $validated_data['enable_cache']);
            $this->Config->set('cache.configurations.'.$this->Config->get('cache.default').'.expire', $validated_data['cache_life']);

            $this->Config->save();

            # If permalinks were changed - reset all post slugs
            if ($oldPermalinks !== $permalinks['permalinks'])
            {
                $this->resetPostSlugs();
            }

            return $this->postMessage('success', 'Kanso settings successfully updated!');
        }

        return false;
    }

    /**
     * Validate cache lifetime
     *
     * @access private
     * @param  string  $cacheLife  A cache life - e.g '3 hours'
     * @return bool
     */
    private function validateCacheLife(string $cacheLife): bool
    {
        if ($cacheLife === '')
        {
            return false;
        }
        else if (is_numeric($cacheLife))
        {
            return true;
        }
        else if (strtotime($cacheLife))
        {
            return true;
        }
        
        $times = [
            'second' => true,
            'minute' => true,
            'hour'   => true,
            'week'   => true,
            'day'    => true,
            'month'  => true,
            'year'   => true
        ];
        
        $life = array_map('trim', explode(' ', $cacheLife));
        
        if (count($life) !== 2)
        {
            return false;
        }
        else if (!is_numeric($life[0]))
        {
            return false;
        }
        
        $time = intval($life[0]);
        
        $life = rtrim($life[1], 's'); 

        if ($time == 0)
        {
            return false;
        }
        
        if (!isset($times[$life]))
        {
            return false;
        }

        return true;
    }

    /**
     * Filter the cache life to a valid timestamp
     *
     * @access private
     * @param  mixed   $cacheLife The url to be converted
     * @return string
     */
    private function filterCacheLife($cacheLife): string
    {
        $default = strtotime('1 week');;

        if (is_numeric($cacheLife))
        {
            return intval($cacheLife);
        }

        else if (strtotime($cacheLife))
        {
            strtotime($cacheLife);
        }

        $times = [
            'second' => true,
            'minute' => true,
            'hour'   => true,
            'week'   => true,
            'day'    => true,
            'month'  => true,
            'year'   => true
        ];
        $life = array_map('trim', explode(' ', $cacheLife));
        
        if (count($life) !== 2)
        {
            return $default;
        }
        if (!is_numeric($life[0]))
        {
            return $default;
        }
        
        $time = intval($life[0]);
        $life = rtrim($life[1], 's'); 

        if ($time == 0)
        {
            return $default;
        }

        if (!isset($times[$life]))
        {
            return $default;
        }

        $life = $time > 1 ? $life.'s' : $life;

        return strtotime($time.' '.$life);
    }

    /**
     * Validate a permalink value
     *
     * @access private
     * @param  string $url The url to be converted
     * @return bool
     */
    private function validatePermalinks(string $url): bool
    {
        $permaLink = '';
        $route     = '';
        $urlPieces = explode('/', $url);
        $map = 
        [
            'year'     => '(:year)',
            'month'    => '(:month)',
            'day'      => '(:day)',
            'hour'     => '(:hour)',
            'minute'   => '(:minute)',
            'second'   => '(:second)',
            'postname' => '(:postname)',
            'category' => '(:category)',
            'author'   => '(:author)',
        ];

        foreach ($urlPieces as $key)
        {
            if (isset($map[$key]))
            {
                $permaLink .= $key.DIRECTORY_SEPARATOR;
                $route     .= $map[$key].DIRECTORY_SEPARATOR;
            }
        }

        if ($permaLink === '' || $route === '' || strpos($permaLink, 'postname') === false)
        {
            return false;
        }

        return true;
    }

    /**
     * Filter the permalinks
     *
     * @param  string $url The url to be converted
     * @return array  Array with the the actual link and the route
     */
    private function filterPermalinks($url) 
    {
        $permaLink = '';
        $route     = '';
        $urlPieces = explode('/', $url);
        $map = [
            'year'     => '(:year)',
            'month'    => '(:month)',
            'day'      => '(:day)',
            'hour'     => '(:hour)',
            'minute'   => '(:minute)',
            'second'   => '(:second)',
            'postname' => '(:postname)',
            'category' => '(:category)',
            'author'   => '(:author)',
        ];
        
        foreach ($urlPieces as $key)
        {
            if (isset($map[$key]))
            {
                $permaLink .= $key.DIRECTORY_SEPARATOR;
                $route     .= $map[$key].DIRECTORY_SEPARATOR;
            }
            else
            {
                $permaLink .= Str::slug($key).DIRECTORY_SEPARATOR;
                $route     .= Str::slug($key).DIRECTORY_SEPARATOR;
            }
        }

        $permaLink = trim($permaLink, '/').'/';
        $route     = trim($route, '/').'/';
        
        return [
            'permalinks' => $permaLink,
            'permalinks_route' => $route,
        ];
    }

    /**
     * Parse, validate and process the add new user form
     * 
     * @access private
     * @return array||false
     */
    private function submitInviteUser()
    {
        # Validate the user is an admin
        if (!$this->Gatekeeper->getUser()->role === 'administrator')
        {
            return false;
        }

        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'email' => 'required|valid_email',
            'role'  => 'required|contains, administrator writer',
        ]);

        $this->validation->filter_rules([
            'email' => 'trim|sanitize_email',
            'role'  => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($post);

        if (!$validated_data)
        {
            return false;
        }

        if ($this->Gatekeeper->getUser()->email === $validated_data['email'])
        {
            return $this->postMessage('warning', 'Another user is already registered with that email address.');
        }

        $user = $this->UserManager->byEmail($validated_data['email']);

        if ($user && $user->status === 'confirmed')
        {
            return $this->postMessage('warning', 'Another user is already registered with that email address.');
        }

        # If theyre deleted or pending re-invite them
        if (!$user || ($user && $user->status !== 'confirmed'))
        {
            if ($this->UserManager->createAdmin($validated_data['email'], $validated_data['role']))
            {
                return $this->postMessage('success', 'The user was successfully sent a registration invite.');
            }
            
        }

        return false;
    }

    /**
     * Parse, validate and process the delete user form
     * 
     * @access private
     * @return array||false
     */
    private function submitDeleteUser()
    {
        # Validate the user is an admin
        if (!$this->Gatekeeper->getUser()->role === 'administrator')
        {
            return false;
        }
       
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'user_id' => 'required|numeric',
        ]);

        $this->validation->filter_rules([
            'user_id' => 'trim|sanitize_numbers',
        ]);

        $validated_data = $this->validation->run($post);

        if (!$validated_data)
        {
            return false;
        }

        $user_id = intval($validated_data['user_id']);

        if ($user_id === $this->Gatekeeper->getUser()->id || $user_id === 1)
        {
            return false;
        }

        $user = $this->UserManager->byId($user_id);

        if ($user)
        {
            $user->delete();

            return $this->postMessage('success', 'The user was successfully deleted.');
        }

        return false;
    }

    /**
     * Parse, validate and process the change user role form
     * 
     * @access private
     * @return array||false
     */
    private function submitChangeUserRole()
    {
        # Validate the user is an admin
        if (!$this->Gatekeeper->getUser()->role === 'administrator')
        {
            return false;
        }

        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'user_id' => 'required|numeric',
            'role'    => 'required|contains, administrator writer',
        ]);

        $this->validation->filter_rules([
            'user_id' => 'trim|sanitize_numbers',
            'role'    => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($post);

        if (!$validated_data)
        {
            return false;
        }

        $user_id = intval($validated_data['user_id']);

        if ($user_id === $this->Gatekeeper->getUser()->id || $user_id === 1)
        {
            return false;
        }

        $user = $this->UserManager->byId($user_id);

        if ($user)
        {
            $user->role = $validated_data['role'];

            $user->save();

            return $this->postMessage('success', 'The user was successfully deleted.');
        }

        return false;
    }

    /**
     * Update and reset post slugs when permalinks have changed
     * 
     * @access private
     * @return 
     */
    private function resetPostSlugs()
    {
        # Select the posts
        $posts = $this->SQL->SELECT('posts.id')->FROM('posts')->FIND_ALL();

        foreach ($posts as $row)
        {
            $post = $this->PostManager->byId($row['id']);

            $post->save();
        }
    }

    /**
     * Parse, validate and process the restore kanso form
     * 
     * @access private
     * @return array||null
     */
    private function submitRestoreKanso()
    {
        # Validate the user is an admin
        if ($this->Gatekeeper->getUser()->role === 'administrator')
        {
            if ($this->Installer->reInstall())
            {
                $this->Response->session()->destroy();

                $this->Response->cookie()->destroy();

                $this->Response->redirect($this->Request->environment()->HTTP_HOST.'/admin/login/');

                return;
            }  
        }

        return $this->postMessage('danger', 'There was an error processing your request.');
    }
}
