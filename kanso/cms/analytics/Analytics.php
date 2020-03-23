<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\analytics;

use kanso\cms\ecommerce\ShoppingCart;
use kanso\framework\mvc\model\Model;
use kanso\framework\utility\Money;

/**
 * Google/Facebook Analytics Utility.
 *
 * @author Joe J. Howard
 */
class Analytics extends Model
{
    /**
     * Is Google analytics enabled.
     *
     * @var bool
     */
    private $gAnalyticsEnabled;

    /**
     * Is Google adwords enabled.
     *
     * @var bool
     */
    private $adwordsEnabled;

    /**
     * Is Facebook pixel enabled.
     *
     * @var bool
     */
    private $fbEnabled;

    /**
     * Google analytics tracking id.
     *
     * @var string
     */
    private $gAnalyticsId;

    /**
     * Google adwords tracking id.
     *
     * @var string
     */
    private $gAdwordsId;

    /**
     * Google adwords conversion id.
     *
     * @var string
     */
    private $googleAwCvId;

    /**
     * Facebook pixel tracking id.
     *
     * @var string
     */
    private $fbPixelId;

    /**
     * Constructor.
     *
     * @param bool   $gAnalyticsEnabled Enable or disable google analytics
     * @param string $gAnalyticsId      Google analytics tracking id
     * @param bool   $adwordsEnabled    Enable or disable google adwords
     * @param string $gAdwordsId        Google adwords tracking id
     * @param string $googleAwCvId      Google adwords conversion id
     * @param bool   $fbEnabled         Enable or disable fb pixel
     * @param string $fbPixelId         Facebook pixel tracking id
     */
    public function __construct(bool $gAnalyticsEnabled, string $gAnalyticsId, bool $adwordsEnabled, string $gAdwordsId, string $googleAwCvId, bool $fbEnabled, string $fbPixelId)
    {
        $this->gAnalyticsEnabled = $gAnalyticsEnabled;

        $this->gAnalyticsId = $gAnalyticsId;

        $this->adwordsEnabled = $adwordsEnabled;

        $this->gAdwordsId = $gAdwordsId;

        $this->googleAwCvId = $googleAwCvId;

        $this->fbEnabled = $fbEnabled;

        $this->fbPixelId = $fbPixelId;
    }

    /**
     * Get the main Google Analytics tracking code.
     *
     * @return string
     */
    public function googleTrackingCode(): string
    {
        return $this->cleanWhiteSpace("
        <script async src=\"https://www.googletagmanager.com/gtag/js?id=UA-119334451-1\"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '" . $this->gAnalyticsId . "');
            gtag('config', '" . $this->gAdwordsId . "');
            " . $this->googleUserData() . "
            gtag('event', 'page_view', {'send_to': '" . $this->gAdwordsId . "'} );
        </script>");
    }

    /**
     * Get the main Facebook tracking code.
     *
     * @return string
     */
    public function facebookTrackingCode(): string
    {
        return $this->cleanWhiteSpace("
        <script type=\"text/javascript\">
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '" . $this->fbPixelId . "', " . $this->facebookUserData() . ");
            fbq('track', 'PageView');
        </script><noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id=" . $this->fbPixelId . '&ev=PageView&noscript=1"/></noscript>');
    }

    /**
     * Track a product view for Google Analytics.
     *
     * @return string
     */
    public function googleTrackingProductView(): string
    {
        $this->Query->rewind_posts();

        $post = $this->Query->the_post();

        return $this->gtag('view_item',
        [
            'event_label' => $this->Query->the_title(),
            'items'       =>
            [[
                'id'       => strval($post->id),
                'name'     => $this->Query->the_title(),
                'brand'    => $this->Config->get('cms.site_title'),
                'category' => 'Products > ' . $this->Query->the_categories_list($post->id, ' > '),
                'price'    => $this->Query->the_price(),
                'variant'  => $post->type === 'product' ? $this->Query->the_skus()[0]['name'] : '',
            ]],
        ]);
    }

    /**
     * Track a product view for Facebook.
     *
     * @return string
     */
    public function facebookTrackingProductView(): string
    {
        $this->Query->rewind_posts();

        $post = $this->Query->the_post();

        return $this->fbq('ViewContent',
        [
            'content_name'     => $this->Query->the_title(),
            'content_category' => 'Products > ' . $this->Query->the_categories_list($post->id, ' > '),
            'content_ids'      => [strval($post->id)],
            'content_type'     => 'product',
            'value'            => $this->Query->the_price(),
            'currency'         => $this->Query->the_currency(),
        ]);
    }

    /**
     * Track a products category view for Google.
     *
     * @return string
     */
    public function googleTrackingProductsView(): string
    {
        $this->Query->rewind_posts();

        $listName = $this->Query->the_title();
        $items    = [];

        if ($this->Query->is_page('products'))
        {
            $listName = 'Products';
        }
        elseif ($this->Query->is_page('bundles'))
        {
            $listName = 'Bundles';
        }
        elseif ($this->Query->the_taxonomy())
        {
            $listName = $this->Query->the_taxonomy()->name;
        }

        foreach ($this->Query->the_posts() as $i => $post)
        {
            $items[] =
            [
                'id'            => strval($post->id),
                'name'          => $this->Query->the_title($post->id),
                'list_name'     => $listName,
                'brand'         => $this->Config->get('cms.site_title'),
                'category'      => 'Products > ' . $this->Query->the_categories_list($post->id, ' > '),
                'variant'       => $post->type === 'product' ? $this->Query->the_skus($post->id)[0]['name'] : '',
                'list_position' => $i + 1,
                'quantity'      => 1,
                'price'         =>  $this->Query->the_price($post->id),
            ];
        }

        return $this->gtag('view_item_list',
        [
            'items' => $items,
        ]);
    }

    /**
     * Track a product category view for Facebook.
     *
     * @return string
     */
    public function facebookTrackingProductsView(): string
    {
        $this->Query->rewind_posts();

        $name     = $this->Query->the_title();
        $category = 'Products';
        $ids      = array_map(function($post)
        {
            return strval($post->id);

        }, $this->Query->the_posts());

        if ($this->Query->is_page('products'))
        {
            $name     = 'Products';
            $category = 'Products';
        }
        elseif ($this->Query->is_page('bundles'))
        {
            $name     = 'Bundles';
            $category = 'Bundles';
        }
        elseif ($this->Query->the_taxonomy())
        {
            $name     = $this->Query->the_taxonomy()->name;
            $category = 'Products > ' . $this->Query->the_categories_list(the_post_id(), ' > ');
        }

        return $this->fbq('ViewContent',
        [
            'content_name'     => $name,
            'content_category' => $category,
            'content_ids'      => $ids,
            'content_type'     => 'product',
        ]);
    }

    /**
     * Track a checkout started event for Google Analytics.
     *
     * @return string
     */
    public function googleTrackingStartCheckout(): string
    {
        $items    = [];
        $cart     = $this->Ecommerce->cart()->items();

        foreach($cart as $item)
        {
            $items[] =
            [
                'id'       => strval($item->product_id),
                'name'     => $this->Query->the_title($item->product_id),
                'brand'    => $this->Config->get('cms.site_title'),
                'category' => 'Products > ' . $this->Query->the_categories_list($item->product_id, ' > '),
                'price'    => $item->getSinglePrice(),
                'quantity' => $item->quantity,
                'variant'  => $item->variant,
            ];
        }

        return $this->gtag('begin_checkout',
        [
            'value'    => $this->Ecommerce->cart()->total(),
            'currency' => $this->Ecommerce->cart()->currency(),
            'items'    => $items,
        ]);
    }

    /**
     * Track a checkout started event for Facebook Analytics.
     *
     * @return string
     */
    public function facebookTrackingStartCheckout(): string
    {
        $count = 0;
        $items = [];

        foreach($this->Ecommerce->cart()->items() as $item)
        {
            $count += intval($item->quantity);
            $items[] =
            [
                'id'         => strval($item->product_id),
                'quantity'   => $item->quantity,
                'value'      => $item->getSinglePrice(),
            ];
        }

        return $this->fbq('InitiateCheckout',
        [
            'num_items'    => $count,
            'contents'     => $items,
            'content_type' => 'product',
            'value'        => $this->Ecommerce->cart()->total(true),
            'currency'     => $this->Ecommerce->cart()->currency(),
        ]);
    }

    /**
     * Track a checkout complete for Google Analytics.
     *
     * @param  kanso\cms\ecommerce\ShoppingCart $cart          Shopping cart object
     * @param  string                           $transactionId Transaction id
     * @return string
     */
    public function googleTrackCheckoutComplete(ShoppingCart $cart, string $transactionId): string
    {
        $items = [];

        foreach($cart->items() as $i => $item)
        {
            $items[] =
            [
                'id'            => strval($item->product_id),
                'name'          => $item->name,
                'list_name'     => 'Shopping Cart',
                'list_position' => $i + 1,
                'brand'         => $this->Config->get('cms.site_title'),
                'category'      => 'Products > ' . $this->Query->the_categories_list($item->product_id, ' > '),
                'price'         => $item->getSinglePrice(),
                'quantity'      => $item->quantity,
                'variant'       => $item->variant,
            ];
        }

        $purchase =
        [
            'transaction_id' => $transactionId,
            'value'          => $cart->total(),
            'tax'            => $cart->tax(),
            'shipping'       => $cart->shippingCost(),
            'items'          => $items,
        ];

        $conversion =
        [
            'send_to'        => $this->googleAwCvId,
            'value'          => $cart->total(),
            'currency'       => $cart->currency(),
            'transaction_id' => $transactionId,
        ];

        return $this->gtag('purchase', $purchase) . PHP_EOL . $this->gtag('conversion', $conversion);
    }

    /**
     * Track a checkout complete for Facebook.
     *
     * @param  kanso\cms\ecommerce\ShoppingCart $cart Shopping cart object
     *                                                string                           $transactionId Transaction id
     * @return string
     */
    public function facebookTrackCheckoutComplete(ShoppingCart $cart, string $transactionId): string
    {
        $contents = [];

        foreach($cart->items() as $item)
        {
            $contents[] =
            [
                'id'         => strval($item->product_id),
                'quantity'   => $item->quantity,
                'item_price' => Money::format($item->getSinglePrice(), $cart->currency()),
            ];
        }

        $purchase =
        [
            'contents'     => $contents,
            'content_type' => 'product',
            'value'        => $cart->total(true),
            'currency'     => $cart->currency(),
        ];

        return $this->fbq('Purchase', $purchase);
    }

    /**
     * Get google user id.
     *
     * @return string
     */
    private function googleUserData(): string
    {
        if ($this->Gatekeeper->isLoggedIn())
        {
            return "gtag('set', {'user_id': '" . $this->Gatekeeper->getUser()->id . "'});";
        }

        return '';
    }

    /**
     * Get facebook user data.
     *
     * @return string
     */
    private function facebookUserData(): string
    {
        if ($this->Gatekeeper->isLoggedIn())
        {
            $names     = explode(' ', $this->Gatekeeper->getUser()->name);
            $firstname = trim(ucwords(array_shift($names)));
            $lastname  = trim(implode(' ', $names));
            $email     = $this->Gatekeeper->getUser()->email;
            $fbUser    =
            [
                'em' => $email,
                'fn' => $firstname,
                'ln' => $lastname,
            ];

            return json_encode($fbUser);
        }
        else
        {
            return '{}';
        }
    }

    /**
     * Format HTML nicely.
     *
     * @param  string $html HTML to format
     * @return string
     */
    private function fbq(string $event, array $eventObj): string
    {
        $script  = '<script type="text/javascript">' . PHP_EOL;
        $script .= 'fbq(\'track\', \'' . $event . '\',  ' . PHP_EOL;
        $script .= str_replace('\u003E', '>', json_encode($eventObj, JSON_PRETTY_PRINT)) . ');' . PHP_EOL;
        $script .= '</script>' . PHP_EOL;

        return $script;
    }

    /**
     * Format HTML nicely.
     *
     * @param  string $html HTML to format
     * @return string
     */
    private function gtag(string $event, array $eventObj): string
    {
        $script  = '<script type="text/javascript">' . PHP_EOL;
        $script .= 'gtag(\'event\', \'' . $event . '\',  ' . PHP_EOL;
        $script .= str_replace('\u003E', '>', json_encode($eventObj, JSON_PRETTY_PRINT)) . ');' . PHP_EOL;
        $script .= '</script>' . PHP_EOL;

        return $script;
    }

    /**
     * Format HTML nicely.
     *
     * @param  string $html HTML to format
     * @return string
     */
    private function cleanWhiteSpace(string $html)
    {
        return trim(preg_replace('/\t+/', '', $html)) . "\n";
    }
}
