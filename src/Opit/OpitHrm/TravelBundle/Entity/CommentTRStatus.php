<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\OpitHrm\CoreBundle\Entity\AbstractComment;

/**
 * The child class used for travel request status comments. This class can be extended
 * with properties specific to travel request comments if needed.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="opithrm_tr_status_comments")
 * @ORM\Entity
 */
class CommentTRStatus extends AbstractComment
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $content;

    /**
     * @ORM\OneToOne(targetEntity="StatesTravelRequests", inversedBy="comment")
     * @ORM\JoinColumn(name="states_tr_id", referencedColumnName="id")
     */
    protected $status;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return CommentTRStatus
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set status
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests $status
     * @return CommentTRStatus
     */
    public function setStatus(\Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests
     */
    public function getStatus()
    {
        return $this->status;
    }
}
