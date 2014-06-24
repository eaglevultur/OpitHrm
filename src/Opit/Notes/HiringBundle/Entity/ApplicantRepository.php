<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Description of JobPositionRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
class ApplicantRepository extends EntityRepository
{
    /**
     * @param array $parameters
     * @return object
     */
    public function findAllByFiltersPaginated($pagnationParameters, $parameters = array())
    {
        $orderParams = isset($parameters['order']) ? $parameters['order'] : array();
        $searchParams = isset($parameters['search']) ? $parameters['search'] : array();

        $dq = $this->createQueryBuilder('a')
            ->innerJoin('a.jobPosition', 'jp');

        if (isset($searchParams['name']) && $searchParams['name'] !== '') {
            $dq->andWhere('a.name LIKE :name');
            $dq->setParameter(':name', '%'.$searchParams['name'].'%');
        }

        if (isset($searchParams['email']) && $searchParams['email'] !== '') {
            $dq->andWhere('a.email LIKE :email');
            $dq->setParameter(':email', '%'.$searchParams['email'].'%');
        }

        if (isset($searchParams['phoneNumber']) && $searchParams['phoneNumber'] !== '') {
            $dq->andWhere('a.phoneNumber LIKE :phoneNumber');
            $dq->setParameter(':phoneNumber', '%'.$searchParams['phoneNumber'].'%');
        }

        if (isset($searchParams['keywords']) && $searchParams['keywords'] !== '') {
            $dq->andWhere('a.keywords LIKE :keywords');
            $dq->setParameter(':keywords', '%'.$searchParams['keywords'].'%');
        }

        if (isset($searchParams['applicationDate']) && $searchParams['applicationDate'] !== '') {
            $dq->andWhere('a.applicationDate LIKE :applicationDate');
            $dq->setParameter(':applicationDate', '%'.$searchParams['applicationDate'].'%');
        }

        if (isset($searchParams['jobTitle']) && $searchParams['jobTitle'] !== '') {
            $dq->andWhere('jp.jobTitle LIKE :jobTitle');
            $dq->setParameter(':jobTitle', '%'.$searchParams['jobTitle'].'%');
        }

        if (isset($orderParams['field']) && $orderParams['field'] && isset($orderParams['dir']) && $orderParams['dir']) {
            $dq->orderBy($orderParams['field'], $orderParams['dir']);
        }

        $dq->setFirstResult($pagnationParameters['firstResult']);
        $dq->setMaxResults($pagnationParameters['maxResults']);

        return new Paginator($dq->getQuery(), true);
    }
}