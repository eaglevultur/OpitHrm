<?php

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Opit\OpitHrm\CoreBundle\Entity\AbstractBase;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * LeaveRequest
 *
 * @ORM\Table(name="opithrm_leave_request")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\LeaveBundle\Entity\LeaveRequestRepository")
 */
class LeaveRequest extends AbstractBase
{

    const ID_PATTERN = 'LR-{year}-{id}';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Leave", mappedBy="leaveRequest", cascade={"persist", "remove"})
     * @Assert\Valid
     */
    protected $leaves;

    /**
     * @ORM\ManyToOne(targetEntity="LeaveRequestGroup", inversedBy="leaveRequests")
     * @ORM\JoinColumn(name="leave_request_group_id", referencedColumnName="id")
     */
    protected $leaveRequestGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\UserBundle\Entity\Employee", inversedBy="leaveRequests")
     * @Assert\NotBlank(message="Employee cannot be empty.")
     */
    protected $employee;

    /**
     * @var text
     * @ORM\Column(name="leave_request_id", type="string", length=11, nullable=true)
     */
    protected $leaveRequestId;

    /**
     * @var text
     */
    protected $parentLeaveRequestId;

    /**
     * @ORM\OneToMany(targetEntity="StatesLeaveRequests", mappedBy="leaveRequest", cascade={"persist", "remove"})
     */
    protected $states;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\UserBundle\Entity\User", inversedBy="gmLeaveRequests")
     * @Assert\NotBlank(message="General manager cannot be empty.")
     */
    protected $generalManager;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\UserBundle\Entity\User", inversedBy="tmLeaveRequests")
     */
    protected $teamManager;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isMassLeaveRequest;

    /**
     * @ORM\OneToMany(targetEntity="LRNotification", mappedBy="leaveRequest", cascade={"remove"})
     */
    protected $notifications;
    protected $isOverlapped;
    protected $rejectedGmName;
    protected $isCreatedByGM;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->leaves = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isMassLeaveRequest = false;
    }

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
     * Set leaveRequestId

     * @param string $leaveRequestId
     * @return LeaveRequest
     */
    public function setLeaveRequestId($leaveRequestId = null)
    {
        $this->leaveRequestId = $leaveRequestId;

        return $this;
    }

    /**
     * Get leave request id
     *
     * @return string
     */
    public function getLeaveRequestId()
    {
        return $this->leaveRequestId;
    }

    /**
     * Set parentLeaveRequestId

     * @param string $parentLeaveRequestId
     * @return LeaveRequest
     */
    public function setParentLeaveRequestId($parentLeaveRequestId = null)
    {
        $this->parentLeaveRequestId = $parentLeaveRequestId;

        return $this;
    }

    /**
     * Get leave request id
     *
     * @return string
     */
    public function getParentLeaveRequestId()
    {
        return $this->parentLeaveRequestId;
    }

    /**
     * Add leaves
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\Leave $leaves
     * @return LeaveRequest
     */
    public function addLeaf(\Opit\OpitHrm\LeaveBundle\Entity\Leave $leaves)
    {
        $this->leaves[] = $leaves;
        $leaves->setLeaveRequest($this); // synchronously updating inverse side

        return $this;
    }

    /**
     * Remove leaves
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\Leave $leaves
     */
    public function removeLeaf(\Opit\OpitHrm\LeaveBundle\Entity\Leave $leaves)
    {
        $this->leaves->removeElement($leaves);
    }

    /**
     * Get leaves
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeaves()
    {
        return $this->leaves;
    }

    /**
     * Set employee
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\Employee $employee
     * @return LeaveRequest
     */
    public function setEmployee(\Opit\OpitHrm\UserBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return \Opit\OpitHrm\UserBundle\Entity\Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * Add states
     *
     * @param StatesLeaveRequests $states
     * @return TravelRequest
     */
    public function addState(StatesLeaveRequests $states)
    {
        $states->setLeaveRequest($this); // synchronously updating inverse side
        $this->states[] = $states;

        return $this;
    }

    /**
     * Remove states
     *
     * @param StatesLeaveRequests $states
     */
    public function removeState(StatesLeaveRequests $states)
    {
        $this->states->removeElement($states);
    }

    /**
     * Get states
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Get generalManager
     *
     * @return \Opit\OpitHrm\UserBundle\Entity\User
     */
    public function getGeneralManager()
    {
        return $this->generalManager;
    }

    /**
     * Set generalManager
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\User $generalManager
     * @return LeaveRequest
     */
    public function setGeneralManager(\Opit\OpitHrm\UserBundle\Entity\User $generalManager = null)
    {
        $this->generalManager = $generalManager;

        return $this;
    }

    /**
     * Get generalManager
     *
     * @return \Opit\OpitHrm\UserBundle\Entity\User
     */
    public function getTeamManager()
    {
        return $this->teamManager;
    }

    /**
     * Set generalManager
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\User $teamManager
     * @return LeaveRequest
     */
    public function setTeamManager(\Opit\OpitHrm\UserBundle\Entity\User $teamManager = null)
    {
        $this->teamManager = $teamManager;

        return $this;
    }

    /**
     * Add notifications
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LRNotification $notifications
     * @return TravelRequest
     */
    public function addNotification(\Opit\OpitHrm\LeaveBundle\Entity\LRNotification $notifications)
    {
        $this->notifications[] = $notifications;
        $notifications->setLeaveRequest($this);

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LRNotification $notifications
     */
    public function removeNotification(\Opit\OpitHrm\LeaveBundle\Entity\LRNotification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set leaveRequestGroup
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequestGroup $leaveRequestGroup
     * @return LeaveRequest
     */
    public function setLeaveRequestGroup(\Opit\OpitHrm\LeaveBundle\Entity\LeaveRequestGroup $leaveRequestGroup = null)
    {
        $this->leaveRequestGroup = $leaveRequestGroup;

        return $this;
    }

    /**
     * Get leaveRequestGroup
     *
     * @return \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequestGroup
     */
    public function getLeaveRequestGroup()
    {
        return $this->leaveRequestGroup;
    }

    /**
     * Set isMassLeaveRequest
     *
     * @param boolean $isMassLeaveRequest
     * @return LeaveRequest
     */
    public function setIsMassLeaveRequest($isMassLeaveRequest)
    {
        $this->isMassLeaveRequest = $isMassLeaveRequest;

        return $this;
    }

    /**
     * Get isMassLeaveRequest
     *
     * @return boolean
     */
    public function getIsMassLeaveRequest()
    {
        return $this->isMassLeaveRequest;
    }

    /**
     * Set isOverlapped
     *
     * @param boolean $isOverlapped
     * @return LeaveRequest
     */
    public function setIsOverlapped($isOverlapped)
    {
        $this->isOverlapped = $isOverlapped;

        return $this;
    }

    /**
     * Get isOverlapped
     *
     * @return boolean
     */
    public function getIsOverlapped()
    {
        return $this->isOverlapped;
    }

    /**
     * Set rejectedGmName
     *
     * @param boolean $rejectedGmName
     * @return LeaveRequest
     */
    public function setRejectedGmName($rejectedGmName)
    {
        $this->rejectedGmName = $rejectedGmName;

        return $this;
    }

    /**
     * Get rejectedGmName
     *
     * @return boolean
     */
    public function getRejectedGmName()
    {
        return $this->rejectedGmName;
    }

    /**
     * Returns the leave request pattern for the ID generation
     *
     * @return string The travel entity type
     */
    public static function getIDPattern()
    {
        return self::ID_PATTERN;
    }

    /**
     * Set isCreatedByGM
     *
     * @param boolean $isCreatedByGM
     * @return Leave
     */
    public function setIsCreatedByGM($isCreatedByGM)
    {
        $this->isCreatedByGM = $isCreatedByGM;

        return $this;
    }

    /**
     * Get isCreatedByGM
     *
     * @return boolean
     */
    public function getIsCreatedByGM()
    {
        return $this->isCreatedByGM;
    }

    /**
     * validate leave dates overlapping
     * An existing groups option must use in the assert annotation.
     * This groups must on a property in order to this assert callback works
     *
     * @Assert\Callback
     */
    public function validateLeaveDates(ExecutionContextInterface $context)
    {
        $collection = $this->getLeaves();
        $overlappingDates = array();

        // Checking the date overlapping
        foreach ($collection as $element) {
            $current = $element;

            foreach ($collection as $otherElement) {
                if ($current !== $otherElement) {
                    if (!$otherElement->getStartDate() || !$otherElement->getEndDate()) {
                        continue;
                    }

                    // Checking the date overlapping with other leaves.
                    if (($current->getStartDate() <= $otherElement->getEndDate()) &&
                        ($otherElement->getStartDate() <= $current->getEndDate())) {
                        $overlappingDates[] = array(
                            $otherElement->getStartDate(),
                            $otherElement->getEndDate());
                        break;
                    }
                }
            }
        }

        if (count($overlappingDates) > 0) {
            $context->addViolation(
                sprintf('Leave dates are overlapping')
            );
        }
    }

}
