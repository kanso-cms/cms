<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers;

use kanso\framework\common\MagicArrayAccessTrait;

/**
 * CRM Funnel Section Abstract.
 *
 * @author Joe J. Howard
 */
class Visit extends Wrapper
{
	use MagicArrayAccessTrait;

    /**
     * Saves the current visitor.
     *
     * @access public
     * @return bool
     */
    public function save(): bool
    {
        if (isset($this->data['id']))
        {
            $data = $this->data;

            unset($data['id']);

            $update = $this->SQL->UPDATE('crm_visits')->SET($data)->WHERE('id', '=', $this->data['id'])->QUERY();

            if ($update)
            {
                return true;
            }

            return false;
        }
        else
        {
            // Insert into database
            $this->SQL->INSERT_INTO('crm_visits')->VALUES($this->data)->QUERY();

            $this->data['id'] = intval($this->SQL->connectionHandler()->lastInsertId());

            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
    {
        if (isset($this->data['id']))
        {
            $this->SQL->DELETE_FROM('crm_visits')->WHERE('id', '=', $this->data['id'])->QUERY();

            return true;
        }

        return false;
    }
}
