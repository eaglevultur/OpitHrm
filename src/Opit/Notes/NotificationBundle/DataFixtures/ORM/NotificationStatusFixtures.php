<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\NotificationBundle\DataFixtures\ORM;

use Opit\Notes\NotificationBundle\Entity\NotificationStatus;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\NotificationBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of NotificationStatusFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage NotificationBundle
 */
class NotificationStatusFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $reflectionNS = new \ReflectionClass('Opit\Notes\NotificationBundle\Entity\NotificationStatus');
        $notificationStates = $reflectionNS->getConstants();
        
        foreach ($notificationStates as $name => $id) {
            $notificationStatus = new NotificationStatus();
            $notificationStatus->setId($id);
            $notificationStatus->setNotificationStatusName(strtolower($name));
            $manager->persist($notificationStatus);
        }
        
        $manager->flush();
    }
    
     /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 8; // the order in which fixtures will be loaded
    }
    
    /**
     * 
     * @return array
     */
    protected function getEnvironments()
    {
        return array('prod', 'dev');
    }
}