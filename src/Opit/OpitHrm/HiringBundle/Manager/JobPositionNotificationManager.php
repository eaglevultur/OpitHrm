<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Manager;

use Opit\OpitHrm\NotificationBundle\Manager\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Opit\OpitHrm\HiringBundle\Entity\JobPosition;
use Opit\OpitHrm\HiringBundle\Entity\JPNotification;
use Opit\OpitHrm\NotificationBundle\Entity\NotificationStatus;

/**
 * Description of JobPositionNotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class JobPositionNotificationManager extends NotificationManager
{
    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    /**
     * 
     * @param \Opit\OpitHrm\HiringBundle\Entity\JobPosition $jobPosition
     */
    public function addNewJobPositionNotification(JobPosition $jobPosition)
    {
        $message = 'Job position (' . $jobPosition->getJobPositionId() . ') has been created';
        $notification = new JPNotification();
        $notification->setJobPosition($jobPosition);
        $receiver = $jobPosition->getCreatedUser();

        $notification->setMessage($message);
        $notification->setReceiver($receiver);
        $notification->setDateTime(new \DateTime('now'));
        $notification->setRead($this->entityManager->getRepository('OpitOpitHrmNotificationBundle:NotificationStatus')->find(NotificationStatus::UNREAD));

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

    }
}
