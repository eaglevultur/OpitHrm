<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\NotificationBundle\Manager;

/**
 * Description of BaseNotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage NotificationBundle
 */
class BaseNotificationManager extends NotificationManager
{
    public function __construct($entityManager)
    {
        parent::__construct($entityManager);
    }
}
