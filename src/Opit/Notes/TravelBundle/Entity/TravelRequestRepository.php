<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Opit\Notes\UserBundle\Entity\User;

/**
 * TravelRequestRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TravelRequestRepository extends EntityRepository
{
    /**
     * Get back travel requests by the given search paramteres.
     *
     * @param array $parameters query parameters
     * @return array data of travel requests
     * @todo create search opportunity on TeamManager name
     * @todo create search opportunity on GeneralManager name
     */
    public function getTravelRequestsBySearchParams($parameters)
    {
        /**
         * Params which will be pass to the setParameter function.
         * @var array
         */
        $params = array();
        
        $qb = $this->createQueryBuilder('tr');

        if ($parameters['trId']!="") {
            $params['trId'] = '%'.$parameters['trId'].'%';
            $qb->andWhere($qb->expr()->like('tr.travelRequestId', ':trId'));
        }
        if ($parameters['employeeName']!="") {
            $qb->leftJoin('tr.user', 'u', 'WITH');
            $params['employeeName'] = '%'.$parameters['employeeName'].'%';
            $qb->andWhere($qb->expr()->like('u.employeeName', ':employeeName'));
        }
        if ($parameters['opportunityName']!="") {
            $params['opportunityName'] = '%'.$parameters['opportunityName'].'%';
            $qb->andWhere($qb->expr()->like('tr.opportunityName', ':opportunityName'));
        }
        if ($parameters['destinationName']!="") {
            $params['destinationName'] = '%'.$parameters['destinationName'].'%';
            $qb->leftJoin('tr.destinations', 'd', 'WITH');
            $qb->andWhere($qb->expr()->like('d.name', ':destinationName'));
        }
        if ($parameters['departureDateFrom']!="") {
            $params['departureDateFrom'] = $parameters['departureDateFrom'];
            $qb->andWhere($qb->expr()->gte('tr.departureDate', ':departureDateFrom'));
        }
        if ($parameters['departureDateTo']!="") {
            $params['departureDateTo'] = $parameters['departureDateTo'];
            $qb->andWhere($qb->expr()->lte('tr.departureDate', ':departureDateTo'));
        }
        if ($parameters['arrivalDateFrom']!="") {
            $params['arrivalDateFrom'] = $parameters['arrivalDateFrom'];
            $qb->andWhere($qb->expr()->gte('tr.arrivalDate', ':arrivalDateFrom'));
        }
        if ($parameters['arrivalDateTo']!="") {
            $params['arrivalDateTo'] = $parameters['arrivalDateTo'];
            $qb->andWhere($qb->expr()->lte('tr.arrivalDate', ':arrivalDateTo'));
        }

        $qb->setParameters($params);
        $q = $qb->getQuery();
        return $q->getResult();
    }
    
    /**
     * Find all travel request with ordering by fields.
     * 
     * @param string $field
     * @param string $order
     * @return null|TravelRequest
     */
    public function findAllOrderByField($field, $order)
    {
        if (!isset($field) || !isset($order) ||empty($field) || empty($order)) {
            return null;
        }
        
        $qb = $this->createQueryBuilder('tr');
        
        if ("user"===$field) {
            $qb->leftJoin('tr.user', 'u', 'WITH');
            $qb->orderBy('u.employeeName', $order);
        } else {
             $qb->orderBy('tr.'.$field, $order);
        }
       
        $q = $qb->getQuery();
        return $q->getResult();
    }
}
