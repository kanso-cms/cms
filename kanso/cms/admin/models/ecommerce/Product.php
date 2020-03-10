<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models\ecommerce;

use kanso\framework\mvc\model\Model;
use kanso\framework\utility\Str;

/**
 * Admin Ecommerce product validation for writer.
 *
 * @author Joe J. Howard
 */
class Product extends Model
{
	/**
	 * Parse post data from the writer application on save
	 * and return product offers.
	 *
	 * @param  array $data Raw $_POSST array data
	 * @return array
	 */
	public function parse(array $data): array
    {
    	$offers    = [];
        $offerKeys =
        [
            'product_offer_X_id'            => 'offer_id',
            'product_offer_X_name'          => 'name',
            'product_offer_X_price'         => 'price',
            'product_offer_X_sale_price'    => 'sale_price',
            'product_offer_X_instock'       => 'instock',
            'product_offer_X_free_shipping' => 'free_shipping',
            'product_offer_X_weight'        => 'weight',

        ];

        for ($i=1; $i <= 20; $i++)
        {
            $offer = [];

            foreach ($offerKeys as $postKey => $offerKey)
            {
                $postKey = str_replace('X', strval($i), $postKey);

                if (isset($data[$postKey]))
                {
                    if ($offerKey === 'sale_price' || $offerKey === 'price')
                    {
                        $offer[$offerKey] = floatval($data[$postKey]);
                    }
                    elseif ($offerKey === 'weight')
                    {
                        $offer[$offerKey] = intval($data[$postKey]);
                    }
                    elseif ($offerKey === 'instock' || $offerKey === 'free_shipping')
                    {
                        $offer[$offerKey] = Str::bool($data[$postKey]);
                    }
                    else
                    {
                        $offer[$offerKey] = trim($data[$postKey]);
                    }
                }
            }

            if (!empty($offer))
            {
                $offers[] = $offer;
            }
        }

        return $offers;
    }
}
