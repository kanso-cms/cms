<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace app\models;

use app\models\utility\Emails;
use kanso\framework\common\SqlBuilderTrait;
use kanso\framework\mvc\model\Model;

/**
 * Cron Job Model.
 *
 * @author Joe J. Howard
 */
class Cron extends Model
{
    use SqlBuilderTrait;

    /**
     * Validates the URL key provided the application secret.
     *
     * @access public
     * @return bool
     */
    public function validate(): bool
    {
        return $this->Request->queries('key') === $this->Config->get('application.secret');
    }

    /**
     * Handles abandoned cart emails.
     *
     * @access public
     */
    public function dbMaintenance()
    {
        ini_set('max_execution_time', '120');

        $removed  = 0;
        $visitors = $this->sql()->SELECT('id')->FROM('crm_visitors')->FIND_ALL();

        foreach ($visitors as $id)
        {
            // Find the visitor
            $visitor = $this->Crm->leadProvider()->byKey('id', $id['id']);

            if (!$visitor)
            {
                continue;
            }

            $daysSinceActive = floor($visitor->timeSincePrevVisit() / 86400);

            // Visitors with 0 visits
            if ($visitor->countVisits() === 0)
            {
                $removed++;

                $visitor->delete();
            }
            // 30 days old and less than 3 visits who has
            elseif ($daysSinceActive >= 30 && $visitor->countVisits() <= 3 && empty($visitor->email))
            {
                $removed++;

                $visitor->delete();
            }
        }

        $this->Response->status()->set(200);

        $this->Response->format()->set('txt');

        $this->Response->body()->set('Cron Jobs Completed. Removed ' . $removed . ' visitors.');
    }

    /**
     * Handles email queue processing.
     *
     * @access public
     */
    public function emailQueue()
    {
        $this->Email->queue()->process();

        $this->Response->status()->set(200);

        $this->Response->format()->set('txt');

        $this->Response->body()->set('Cron Jobs Completed');
    }
}
