<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\analytics;

use kanso\framework\mvc\model\Model;

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

        $postId = $this->Query->the_post_id();

        $offer = $this->Ecommerce->products()->offers($postId)[0];

        return $this->cleanWhiteSpace("
        <script type=\"text/javascript\">
            gtag('event', 'view_item',
            {
                'event_label'  : '" . $this->Query->the_title() . "',
                'items'        : [{
                    'id'       : '" . $postId . "',
                    'name'     : '" . $this->Query->the_title() . "',
                    'brand'    : '" . $this->Config->get('cms.site_title') . "',
                    'category' : 'Products > " . $this->Query->the_categories_list($postId, ' > ') . "',
                    'price'    : '" . $offer['sale_price'] . "',
                    'variant'  : '" . $offer['name'] . "',
                }]
            });
        </script>
        ");
    }

    /**
     * Track a product view for Facebook.
     *
     * @return string
     */
    public function facebookTrackingProductView(): string
    {
        $this->Query->rewind_posts();

        $postId = $this->Query->the_post_id();

        return $this->cleanWhiteSpace("
        <script type=\"text/javascript\">
            fbq('track', 'ViewContent',
            {
                content_name     : '" . $this->Query->the_title() . "',
                content_category : 'Products > " . $this->Query->the_categories_list($postId, ' > ') . "',
                content_ids      : ['" . $postId . "'],
                content_type     : 'product',
                value            : " . $this->Ecommerce->products()->offers($postId)[0]['sale_price'] . ",
                currency         : 'AUD'
            });
        </script>");
    }

    /**
     * Track a product category view for Facebook.
     *
     * @return string
     */
    public function facebookTrackingProductsView(): string
    {
        $this->Query->rewind_posts();

        $name     = $this->Query->is_page('products') ? 'Products' : $this->Query->the_taxonomy()->name;
        $category = $this->Query->is_page('products') ? 'Products' : 'Products > ' . $this->Query->the_categories_list(the_post_id(), ' > ');
        $ids      = [];

        foreach ($this->Query->the_posts() as $post)
        {
           $ids[] = strval($post->id);
        }

        return $this->cleanWhiteSpace("
        <script type=\"text/javascript\">
            fbq('trackCustom', 'ViewCategory',
            {
                content_name     : '" . $name . "',
                content_category : '" . $category . "',
                content_ids      : ['" . implode('\',\'', $ids) . "'],
                content_type     : 'product'
            });
        </script>");
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
        $subtotal = $this->Ecommerce->cart()->subtotal();
        $shipping = $this->Ecommerce->cart()->shippingCost();

        foreach($cart as $item)
        {
            $items[] =
            [
                'id'       => strval($item['product']),
                'name'     => $this->Query->the_title($item['product']),
                'brand'    => $this->Config->get('cms.site_title'),
                'category' => 'Products > ' . $this->Query->the_categories_list($item['product'], ' > '),
                'price'    => strval($item['offer']['sale_price']),
                'quantity' => $item['quantity'],
                'variant'  => $item['offer']['name'],
            ];
        }

        return $this->cleanWhiteSpace("
        <script type=\"text/javascript\">
            gtag('event', 'begin_checkout',
            {
                'value'    : '" . number_format(($subtotal + $shipping), 2, '.', '') . "',
                'currency' : 'AUD',
                'items'    : " . str_replace('\u003E', '>', json_encode($items)) . '
            });
        </script>');
    }

    /**
     * Track a checkout started event for Facebook Analytics.
     *
     * @return string
     */
    public function facebookTrackingStartCheckout(): string
    {
        $cart     = $this->Ecommerce->cart()->items();
        $subtotal = $this->Ecommerce->cart()->subtotal();
        $shipping = $this->Ecommerce->cart()->shippingCost();
        $count    = 0;
        $items    = [];

        foreach($cart as $item)
        {
            $count += intval($item['quantity']);
            $items[] =
            [
                'id'         => strval($item['product']),
                'quantity'   => $item['quantity'],
                'item_price' => strval($item['offer']['sale_price']),
            ];
        }

        return $this->cleanWhiteSpace("
        <script type=\"text/javascript\">
            fbq('track', 'InitiateCheckout',
            {
                num_items     : " . $count . ',
                contents      : ' . json_encode($items) . ",
                content_type  : 'product',
                value         : " . number_format(($subtotal + $shipping), 2, '.', '') . ",
                currency      : 'AUD'
            });
        </script>");
    }

    /**
     * Track a checkout complete for Google Analytics.
     *
     * @param  array  $order Transaction row from the database
     * @return string
     */
    public function googleTrackCheckoutComplete(array $order): string
    {
        $items = [];

        foreach($order['items'] as $item)
        {
            $items[] =
            [
                'id'       => strval($item['product_id']),
                'name'     => $item['name'],
                'brand'    => $this->Config->get('cms.site_title'),
                'category' => 'Products > ' . $this->Query->the_categories_list($item['product_id'], ' > '),
                'price'    => strval($item['price']),
                'quantity' => $item['quantity'],
                'variant'  => $item['offer'],
            ];
        }

        return $this->cleanWhiteSpace("
        <script type=\"text/javascript\">
            gtag('event', 'purchase',
            {
                'transaction_id' : '" . $order['bt_transaction_id'] . "',
                'value'          : " . $order['total'] . ",
                'currency'       : 'AUD',
                'tax'            : " . number_format((10 / 100) * $order['total'], 2, '.', '') . ",
                'shipping'       : " . number_format($order['shipping_costs'], 2, '.', '') . ",
                'items'          : " . str_replace('\u003E', '>', json_encode($items)) . "
            });
        </script>
        <script>
            gtag('event', 'conversion',
            {
                'send_to'  : '" . $this->googleAwCvId . "',
                'value'    : " . $order['total'] . ",
                'currency' : 'AUD',
                'transaction_id' : '" . $order['bt_transaction_id'] . "'
            });
        </script>");
    }

    /**
     * Track a checkout complete for Facebook.
     *
     * @param  array  $order Transaction row from the database
     * @return string
     */
    public function facebookTrackCheckoutComplete(array $order): string
    {
        $contents = [];

        foreach($order['items'] as $i => $item)
        {
            $contents[] =
            [
                'id'         => strval($item['product_id']),
                'quantity'   => $item['quantity'],
                'item_price' => floatval($item['price']),
            ];
        }

        return $this->cleanWhiteSpace("
        <script type=\"text/javascript\">
            fbq('track', 'Purchase',
            {
                contents     : " . json_encode($contents) . ",
                content_type : 'product',
                value        : " . $order['total'] . ",
                currency     : 'AUD'
            });
        </script>
        ");
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
    private function cleanWhiteSpace(string $html)
    {
        return trim(preg_replace('/\t+/', '', $html)) . "\n";
    }
}
