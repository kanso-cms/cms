<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models\ecommerce;

use Exception;
use kanso\framework\mvc\model\Model;

/**
 * Admin Ecommerce bundle validation for writer.
 *
 * @author Joe J. Howard
 */
class Bundle extends Model
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
    	if (!isset($data['bundle_type']) || !in_array($data['bundle_type'], ['group', 'bogo', 'combo']))
    	{
    		throw new Exception('Could not parse bundle, the bundle_type is was either not set or incorrect.');
    	}

    	// Group
        if ($data['bundle_type'] === 'group')
        {
            return $this->parseGroup($data);
        }

        // Bogo
        elseif ($data['bundle_type'] === 'bogo')
        {
            return $this->parseBogo($data);
        }

        // Combo
        elseif ($data['bundle_type'] === 'combo')
        {
            return $this->parseCombo($data);
        }
    }

    /**
     * Parse combo bundle options.
     *
     * @param  array $data Raw $_POSST array data
     * @return array
     */
    private function parseCombo(array $data): array
    {
    	$rules =
        [
        	'bundle_combo_fixed_price'   => ['required'],
            'bundle_combo_names'         => ['required', 'json'],
        ];
        $filters =
        [
            'bundle_combo_fixed_price'  => ['trim', 'float'],
            'bundle_combo_names'        => ['trim', 'json'],
        ];

        $count = count(json_decode($data['bundle_combo_names'], true)) + 1;

        for ($i = 1; $i < $count; $i++)
        {
        	$rules['bundle_product_combo_' . $i . '_ids']        = ['required', 'json'];
        	$rules['bundle_product_combo_' . $i . '_quantities'] = ['required', 'json'];
        	$rules['bundle_product_offer_combo_' . $i . '_ids']  = ['required', 'json'];

        	$filters['bundle_product_combo_' . $i . '_ids']         = ['trim', 'json'];
        	$filters['bundle_product_combo_' . $i . '_quantities']  = ['trim', 'json'];
        	$filters['bundle_product_offer_combo_' . $i . '_ids']   = ['trim', 'json'];
        }

        $validator = $this->Validator->create($data, $rules, $filters);

		if (!$validator->isValid())
        {
        	$errors = $validator->getErrors();
        	$msg    = array_shift($errors);

        	throw new Exception('Error parsing bundle configuration. ' . $msg);
        }

        $data     = $validator->filter();
        $response =
        [
        	'type'     => 'combo',
        	'products' => [],
        	'price'    => $data['bundle_combo_fixed_price'],
        ];

        if ($response['price'] === 0)
        {
        	throw new Exception('Error creating bundle. The price cannot be zero.');
        }

        foreach ($data['bundle_combo_names'] as $x => $selectionName)
        {
        	$x          = $x + 1;
        	$selection  = [];
        	$productIds = $data['bundle_product_combo_' . $x . '_ids'];
        	$quantities = $data['bundle_product_combo_' . $x . '_quantities'];
        	$skus       = $data['bundle_product_offer_combo_' . $x . '_ids'];

        	foreach ($productIds as $i => $productId)
        	{
        		$productId = intval($productIds[$i]);
        		$quantity  = intval($quantities[$i]);
        		$skuId   = $skus[$i];
        		$sku     = $this->ProductProvider->sku($productId, $skuId);

	        	if (!$sku)
	    		{
	    			throw new Exception('Error parsing bundle configuration. Could not find a product with id "' . $productId . '" and offer id "' . $skuId . '".');
	    		}

        		$selection[] =
        		[
        			'product_id' => $productId,
	            	'sku'        => $skuId,
	            	'quantity'   => $quantity,
        		];
        	}

        	$response['products'][$selectionName] = $selection;
        }

        return $response;
    }

    /**
     * Parse bogo bundle options.
     *
     * @param  array $data Raw $_POSST array data
     * @return array
     */
    private function parseBogo(array $data): array
    {
    	$rules =
        [
        	'bundle_product_bogo_in_ids'         => ['required', 'json'],
            'bundle_product_bogo_in_quantities'  => ['required', 'json'],
            'bundle_product_offer_bogo_in_ids'   => ['required', 'json'],

            'bundle_product_bogo_out_ids'        => ['required', 'json'],
            'bundle_product_bogo_out_quantities' => ['required', 'json'],
            'bundle_product_offer_bogo_out_ids'  => ['required', 'json'],
        ];
        $filters =
        [
            'bundle_product_bogo_in_ids'         => ['trim', 'json'],
            'bundle_product_bogo_in_quantities'  => ['trim', 'json'],
            'bundle_product_offer_bogo_in_ids'   => ['trim', 'json'],

            'bundle_product_bogo_out_ids'        => ['trim', 'json'],
            'bundle_product_bogo_out_quantities' => ['trim', 'json'],
            'bundle_product_offer_bogo_out_ids'  => ['trim', 'json'],
        ];

        $validator = $this->Validator->create($data, $rules, $filters);

		if (!$validator->isValid())
        {
        	$errors = $validator->getErrors();
        	$msg    = array_shift($errors);

        	throw new Exception('Error parsing bundle configuration. ' . $msg);
        }

        $data     = $validator->filter();
    	$response =
        [
        	'type'         => 'bogo',
        	'products_in'  => [],
        	'products_out' => [],
        ];

        foreach ($data['bundle_product_bogo_in_ids'] as $index => $id)
        {
        	$productId = intval($id);
        	$quantity  = intval($data['bundle_product_bogo_in_quantities'][$index]);
        	$skuId   = $data['bundle_product_offer_bogo_in_ids'][$index];
        	$sku     = $this->ProductProvider->sku($productId, $skuId);

        	if (!$sku)
    		{
    			throw new Exception('Error parsing bundle configuration. Could not find a product with id "' . $productId . '" and offer id "' . $skuId . '".');
    		}

    		$response['products_in'][] =
    		[
    			'product_id' => $productId,
	            'sku'   => $skuId,
	            'quantity'   => $quantity,
    		];
    	}

    	foreach ($data['bundle_product_bogo_out_ids'] as $index => $id)
        {
        	$productId = intval($id);
        	$quantity  = intval($data['bundle_product_bogo_out_quantities'][$index]);
        	$skuId   = $data['bundle_product_offer_bogo_out_ids'][$index];
        	$sku     = $this->ProductProvider->sku($productId, $skuId);

        	if (!$sku)
    		{
    			throw new Exception('Error parsing bundle configuration. Could not find a product with id "' . $productId . '" and offer id "' . $skuId . '".');
    		}

    		$response['products_out'][] =
    		[
    			'product_id' => $productId,
	            'sku'   => $skuId,
	            'quantity'   => $quantity,
    		];
    	}

    	if (empty($response['products_in']) || empty($response['products_out']))
    	{
    		throw new Exception('Error parsing bundle configuration. No products were supplied.');
    	}

    	return $response;
  	}

    /**
     * Parse combo group options.
     *
     * @param  array $data Raw $_POSST array data
     * @return array
     */
    private function parseGroup(array $data): array
    {
    	$rules =
        [
            'bundle_product_quantities' => ['required', 'json'],
            'bundle_product_ids'        => ['required', 'json'],
            'bundle_product_offer_ids'  => ['required', 'json'],
        ];
        $filters =
        [
            'bundle_product_quantities'  => ['trim', 'json'],
            'bundle_product_ids'         => ['trim', 'json'],
            'bundle_product_offer_ids'   => ['trim', 'json'],
            'bundle_percentage_discount' => ['trim', 'float'],
            'bundle_group_fixed_price'   => ['trim', 'float'],
            'bundle_ovveride_cents'      => ['trim', 'float'],
        ];

        $validator = $this->Validator->create($data, $rules, $filters);

		if (!$validator->isValid())
        {
        	$errors = $validator->getErrors();
        	$msg    = array_shift($errors);

        	throw new Exception('Error parsing bundle configuration. ' . $msg);
        }

        $data = $validator->filter();

        $response =
        [
        	'type'           => 'group',
        	'products'       => [],
        	'price'          => 0,
        	'discount'       => 0,
        	'override_cents' => 0,
        ];

        $price = 0;

        foreach ($data['bundle_product_ids'] as $index => $id)
        {
        	$productId = intval($id);
        	$quantity  = intval($data['bundle_product_quantities'][$index]);
        	$skuId     = $data['bundle_product_offer_ids'][$index];
        	$sku       = $this->ProductProvider->sku($productId, $skuId);

        	if (!$sku)
    		{
    			throw new Exception('Error parsing bundle configuration. Could not find a product with id "' . $productId . '" and offer id "' . $skuId . '".');
    		}

    		$price  = $price + ($quantity * $sku['sale_price']);

        	$response['products'][] =
        	[
	        	'product_id' => $productId,
	            'sku'        => $skuId,
	            'quantity'   => $quantity,
	        ];
        }

        // No products were found
        if ($price === 0 || empty($response['products']))
        {
        	throw new Exception('Error parsing bundle configuration. No products were supplied.');
        }

        $response['price'] = $price;

       	// Pricing logic is handled during checkout/cart
       	// as pricing can change unless it's a fixed price bundle
        if ($data['bundle_percentage_discount'] > 0)
        {
        	$response['discount'] = intval($data['bundle_percentage_discount']);
        	$response['price']    = 0;

        	// Rounding cents only applies to a percentage discount
        	if ($data['bundle_ovveride_cents'] > 0)
        	{
        		$response['override_cents'] = intval($data['bundle_ovveride_cents']);
        	}
        }
        elseif ($data['bundle_group_fixed_price'] > 0)
        {
        	$response['price'] = $data['bundle_group_fixed_price'];
        }

        return $response;
    }
}
