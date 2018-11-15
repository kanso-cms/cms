<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\deployment;

use kanso\framework\deployment\webhooks\WebhookInterface;

/**
 * Frameowrk deployment interface.
 *
 * @author Joe J. Howard
 */
class Deployment
{
    /**
     * Webhook interface.
     *
     * @var \kanso\framework\deployment\webhooks\WebhookInterface
     */
    private $webhook;

    /**
     * Constructor.
     *
     * @param \kanso\framework\deployment\webhooks\WebhookInterface $webhook Webhook deployment impelementation
     */
    public function __construct(WebhookInterface $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Update the framework.
     *
     * @return \kanso\framework\deployment\webhooks\WebhookInterface
     */
    public function webhook(): WebhookInterface
    {
        return $this->webhook;
    }

    /**
     * Update the framework.
     *
     * @return mixed
     */
    public function update()
    {
        return $this->webhook->deploy();
    }
}
