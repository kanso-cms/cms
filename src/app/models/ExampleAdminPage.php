<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace app\models;

use kanso\cms\admin\models\BaseModel;

/**
 * This file serves as an example of how to create a model
 * when adding a custom page to the Admin panel
 *
 * @author Joe J. Howard
 */
class ExampleAdminPage extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        # Process any GET requests made here
        # 
        # Return an associative array of variables 
        # to be extracted and made available to the view
        return ['foo' => 'bar'];

        # Returning false sends a 404
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        # Process any POST requests made here
        # 
        # Returning an associative array of variables 
        # here will be sent to the view and made available
        # inside the $POST_RESPONSE variable
        # 
        # $_POST superglobals are available at $this->post
        #
        #
        # Helper function will automatically print a message at the top of the page
        return $this->postMessage('success', 'A POST request was made and processed!');

        # Returning false sends a 404 
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        # Process any AJAX requests here
        # 
        # Returning an associative array will
        # send a JSON response to the client
        
        # Returning false sends a 404 
        return false;
    }
}
