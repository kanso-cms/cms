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
     * {@inheritdoc}
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

    /**
     * Returns all visit actions.
     *
     * @return array
     */
    public function actions(): array
    {
        if (isset($this->data['id']))
        {
            return $this->SQL->SELECT('*')->FROM('crm_visit_actions')->WHERE('visit_id', '=', $this->data['id'])->FIND_ALL();
        }

        return [];
    }

    /**
     * Adds a new action.
     *
     * @param  string $action      The action type
     * @param  string $description The action description
     * @return bool
     */
    public function addAction(string $action, string $description): bool
    {
        if (isset($this->data['id']))
        {
            $row =
            [
                'visit_id'           => $this->data['id'],
                'visitor_id'         => $this->data['visitor_id'],
                'action_name'        => $action,
                'action_description' => $description,
                'page'               => $this->data['page'],
                'date'               => time(),
            ];

            $this->SQL->INSERT_INTO('crm_visit_actions')->VALUES($row)->QUERY();

            return true;
        }

        return false;
    }
}
