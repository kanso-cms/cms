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
    	$skus    = [];
        $skuKeys =
        [
            'product_offer_X_id'            => 'sku',
            'product_offer_X_name'          => 'name',
            'product_offer_X_price'         => 'price',
            'product_offer_X_sale_price'    => 'sale_price',
            'product_offer_X_instock'       => 'instock',
            'product_offer_X_free_shipping' => 'free_shipping',
            'product_offer_X_weight'        => 'weight',

        ];

        for ($i=1; $i <= 20; $i++)
        {
            $sku = [];

            foreach ($skuKeys as $postKey => $skuKey)
            {
                $postKey = str_replace('X', strval($i), $postKey);

                if (isset($data[$postKey]))
                {
                    if ($skuKey === 'sale_price' || $skuKey === 'price')
                    {
                        $sku[$skuKey] = floatval($data[$postKey]);
                    }
                    elseif ($skuKey === 'weight')
                    {
                        $sku[$skuKey] = intval($data[$postKey]);
                    }
                    elseif ($skuKey === 'instock' || $skuKey === 'free_shipping')
                    {
                        $sku[$skuKey] = Str::bool($data[$postKey]);
                    }
                    else
                    {
                        $sku[$skuKey] = trim($data[$postKey]);
                    }
                }
            }

            if (!empty($sku))
            {
                $skus[] = $sku;
            }
        }

        return $skus;
    }
}
