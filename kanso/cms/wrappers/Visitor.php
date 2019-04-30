<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers;

use kanso\framework\common\MagicArrayAccessTrait;
use kanso\framework\utility\Str;
use kanso\framework\utility\UUID;

/**
 * CRM Funnel Section Abstract.
 *
 * @author Joe J. Howard
 */
class Visitor extends Wrapper
{
    use MagicArrayAccessTrait;

    /**
     * Current visit.
     *
     * @var \kanso\cms\wrappers\Visit
     */
    protected $visit;

    /**
     * Regenerates a unique visitor id and returns it.
     *
     * @access public
     * @return string
     */
    public function regenerateId(): string
    {
        $this->data['visitor_id'] = UUID::v4();

        return $this->data['visitor_id'];
    }

    /**
     * Gets the current visit.
     *
     * @access public
     * @return \kanso\cms\wrappers\Visit
     */
    public function visit(): Visit
    {
        return $this->visit;
    }

    /**
     * Adds the current visit. Ends the last one.
     *
     * @access public
     * @param array $row Visit row to save to the database
     */
    public function addVisit(array $row)
    {
        $previousVisit = $this->previousVisit();

        if ($previousVisit && (!$previousVisit->end || $previousVisit->end === 0))
        {
            $previousVisit->end = time();

            $previousVisit->save();
        }

        $this->visit = new Visit($this->SQL, $row);

        $this->visit->save();

        $this->data['last_active'] = time();

        $this->save();
    }

    /**
     * Is this visitor a lead?
     *
     * @access public
     * @return bool
     */
    public function isLead(): bool
    {
        return !empty($this->data['email']);
    }

    /**
     * Checks if this is the first visit.
     *
     * @access public
     * @return bool
     */
    public function isFirstVisit(): bool
    {
        return $this->previousVisit() === false;
    }

    /**
     * Get all visits (newest first).
     *
     * @access public
     * @return array
     */
    public function visits(): array
    {
        $result = [];

        $visits = $this->SQL->SELECT('*')->FROM('crm_visits')->WHERE('visitor_id', '=', $this->data['visitor_id'])->ORDER_BY('date', 'DESC')->FIND_ALL();

        foreach ($visits as $visit)
        {
           $result[] = new Visit($this->SQL, $visit);
        }

        return $result;
    }

    /**
     * Count number of visits.
     *
     * @access public
     * @return int
     */
    public function countVisits(): int
    {
        return count($this->SQL->SELECT('visitor_id')->FROM('crm_visits')->WHERE('visitor_id', '=', $this->data['visitor_id'])->FIND_ALL());
    }

    /**
     * Gets a visitor's most recent visit (excluding the current one).
     *
     * @access public
     * @return \kanso\cms\wrappers\Visit|false
     */
    public function previousVisit()
    {
        if (isset($this->data['id']))
        {
            $visit = $this->SQL->SELECT('*')->FROM('crm_visits')->WHERE('visitor_id', '=', $this->data['visitor_id'])->ORDER_BY('date', 'DESC')->LIMIT(1, 1)->ROW();

            if ($visit)
            {
                return new Visit($this->SQL, $visit);
            }
        }
        
        return false;
    }

    /**
     * Calculates the time since their previous visit.
     *
     * @access public
     * @return int
     */
    public function timeSincePrevVisit(): int
    {
        $previousVisit = $this->previousVisit();

        if ($previousVisit)
        {
            return time() - $previousVisit->date;
        }

        return 0;
    }

    /**
     * Gets a their first visit.
     *
     * @access public
     * @return \kanso\cms\wrappers\Visit
     */
    public function firstVisit(): Visit
    {
        return new Visit($this->SQL, $this->SQL->SELECT('*')->FROM('crm_visits')->WHERE('visitor_id', '=', $this->data['visitor_id'])->ORDER_BY('date', 'ASC')->ROW());
    }

    /**
     * Mark visitor as still active on page.
     * Sets a visitor's last active to now.
     *
     * @access public
     */
    public function heartBeat()
    {
        $previousVisit = $this->previousVisit();

        if ($previousVisit)
        {
            $previousVisit->end = time();

            $previousVisit->save();
        }

        $this->data['last_active'] = time();

        $this->save();
    }

    /**
     * Makes visitor a lead.
     *
     * @access public
     * @param  string $email Email address to subscribe
     * @param  string $name  Persons name (optional) (default '')
     * @return bool
     */
    public function makeLead(string $email, string $name = ''): bool
    {
        $this->data['email'] = $email;

        if ($name)
        {
            $this->data['name'] = $name;
        }

        $this->save();

        return true;
    }

    /**
     * Did this visitor bounce?
     *
     * @access public
     * @return bool
     */
    public function bounced(): bool
    {
        return $this->countVisits() <= 1;
    }

    /**
     * What is the visitor's initial channel entry.
     *
     * @access public
     * @return string
     */
    public function channel(): string
    {
        $visit = $this->firstVisit();

        if ($visit && !empty($visit->page))
        {
            $queryStr = Str::getAfterLastChar($visit->page, '?');

            if ($queryStr !== $visit->page)
            {
                $querySets = explode('&', trim($queryStr, '/'));

                if (!empty($querySets))
                {
                    foreach ($querySets as $querySet)
                    {
                        if (Str::contains($querySet, '='))
                        {
                            $querySet = explode('=', $querySet);
                            $key      = urldecode($querySet[0]);
                            $value    = urldecode($querySet[1]);

                            if ($key === 'ch')
                            {
                                return $value;
                            }
                        }
                    }
                }
            }
        }

        return 'direct';
    }

    /**
     * What is the visitor's initial medium entry.
     *
     * @access public
     * @return string
     */
    public function medium(): string
    {
        $visit = $this->firstVisit();

        if ($visit && !empty($visit->page))
        {
            $queryStr = Str::getAfterLastChar($visit->page, '?');

            if ($queryStr !== $visit->page)
            {
                $querySets = explode('&', trim($queryStr, '/'));

                if (!empty($querySets))
                {
                    foreach ($querySets as $querySet)
                    {
                        if (Str::contains($querySet, '='))
                        {
                            $querySet = explode('=', $querySet);
                            $key      = urldecode($querySet[0]);
                            $value    = urldecode($querySet[1]);

                            if ($key === 'md')
                            {
                                return $value;
                            }
                        }
                    }
                }
            }
        }

        return 'none';
    }

    /**
     * Grades the current visitor or a visitor.
     *
     * 1. Visitor
     * 2. Lead
     * 3. SQL
     *
     * @access public
     * @param  \kanso\cms\wrappers\Visitor|null $visitor      Visitor to grade (optional) (default null)
     * @param  bool                             $returnString Return score as string (optional) (default false)
     * @return string|int
     */
    public function grade(Visitor $visitor = null, bool $returnString = false)
    {
        $visitor = !$visitor ? $this : $visitor;

        $visitCount = $visitor->countVisits();

        // Visitor is not a lead
        if (!$visitor->email)
        {
            return $returnString ? 'visitor' : 1;
        }
        // Visitor is a lead
        else
        {
            // SQL
            if ($visitCount >= 8)
            {
                return $returnString ? 'sql' : 3;
            }

            // Lead
            return $returnString ? 'lead' : 2;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool
    {
        if (isset($this->data['id']))
        {
            $data = $this->data;

            unset($data['id']);

            unset($data['visit']);

            $update = $this->SQL->UPDATE('crm_visitors')->SET($data)->WHERE('id', '=', $this->data['id'])->QUERY();

            if ($update)
            {
                return true;
            }

            return false;
        }
        else
        {
            $data = $this->data;

            unset($data['visit']);

            // Insert into database
            $this->SQL->INSERT_INTO('crm_visitors')->VALUES($data)->QUERY();

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
            $this->SQL->DELETE_FROM('crm_visitors')->WHERE('id', '=', $this->data['id'])->QUERY();

            $this->SQL->DELETE_FROM('crm_visitors')->WHERE('visitor_id', '=', $this->data['visitor_id'])->QUERY();

            $this->SQL->DELETE_FROM('crm_visits')->WHERE('visitor_id', '=', $this->data['visitor_id'])->QUERY();

            return true;
        }

        return false;
    }
}
