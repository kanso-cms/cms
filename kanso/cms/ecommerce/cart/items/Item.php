<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\cart\items;

/**
 * Shopping cart item base class.
 *
 * @author Joe J. Howard
 */
abstract class Item
{
	use CartItemTrait;

    /**
     * Constructor.
     *
     * @param array $data Array of data
     */
    public function __construct(array $data)
    {
        if (is_array($this->defaults))
        {
            $data = array_merge($this->defaults, $data);
        }

        foreach ($data as $k => $v)
        {
            $this->$k = $v;
        }
    }

    /**
     * Export the cart item as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return
        [
            'id'    => $this->getId(),
            'class' => get_class($this),
            'data'  => $this->data,
        ];
    }
}
