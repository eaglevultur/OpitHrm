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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Opit\OpitHrm\TravelBundle\Entity\TravelExpense;
use Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests;
use Opit\OpitHrm\TravelBundle\Model\TravelResourceInterface;

/**
 * travel_request
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="opithrm_travel_request")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\TravelBundle\Entity\TravelRequestRepository")
 */
class TravelRequest implements TravelResourceInterface
{
    const TYPE = 'tr';
    const ID_PATTERN = 'TR-{year}-{id}';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
      * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface", inversedBy="userTravelRequests")
      * @Assert\NotBlank(message="Employee name cannot be empty.")
      * @var TravelRequestUserInterface
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="departure_date", type="date")
     * @Assert\NotBlank(message="Departure date cannot be empty.")
     * @Assert\Date()
     */
    private $departureDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="arrival_date", type="date")
     * @Assert\NotBlank(message="Arrival date cannot be empty.")
     * @Assert\Date()
     */
    private $arrivalDate;

    /**
     * @var string
     *
     * @ORM\Column(name="trip_purpose", type="string", length=255)
     * @Assert\NotBlank(message="Trip purpose cannot be empty.")
     */
    private $tripPurpose;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface", inversedBy="tmTravelRequests")
     * @var TravelRequestUserInterface
     */
    private $teamManager;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface", inversedBy="gmTravelRequests")
     * @Assert\NotBlank(message="General manager cannot be empty.")
     * @var TravelRequestUserInterface
     */
    private $generalManager;

    /**
     * @var boolean
     *
     * @ORM\Column(name="customer_related", type="boolean")
     */
    private $customerRelated;

    /**
     * @var string
     * @ORM\Column(name="customer_name", type="string", nullable=true)
     */
    private $customerName;

    /**
     * @ORM\OneToMany(targetEntity="TRDestination", mappedBy="travelRequest", cascade={"persist", "remove"})
     * @Assert\NotBlank(message="Destinations cannot be empty.")
     */
    private $destinations;

    /**
     * @ORM\OneToMany(targetEntity="TRAccomodation", mappedBy="travelRequest", cascade={"persist", "remove"})
     * @Assert\NotBlank(message="Accomodations date cannot be empty.")
     */
    private $accomodations;

    /**
     * @var text
     * @ORM\Column(name="travel_request_id", type="string", length=11, nullable=true)
     */
    private $travelRequestId;

    /**
     * @var TravelExpense
     * @ORM\OneToOne(targetEntity="TravelExpense", mappedBy="travelRequest", cascade={"persist", "remove"})
     */
    private $travelExpense;

    /**
     * @ORM\OneToMany(targetEntity="StatesTravelRequests", mappedBy="travelRequest", cascade={"persist", "remove"})
     */
    protected $states;

    /**
     * @ORM\OneToMany(targetEntity="TRNotification", mappedBy="travelRequest", cascade={"remove"})
     */
    protected $notifications;

    public function __construct()
    {
        $this->destinations = new ArrayCollection();
        $this->accomodations = new ArrayCollection();
        $this->states = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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
     * Set departureDate
     *
     * @param \DateTime $departureDate
     * @return travel_request
     */
    public function setDepartureDate($departureDate)
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    /**
     * Get departureDate
     *
     * @return \DateTime
     */
    public function getDepartureDate()
    {
        return $this->departureDate;
    }

    /**
     * Set arrivalDate
     *
     * @param \DateTime $arrivalDate
     * @return travel_request
     */
    public function setArrivalDate($arrivalDate)
    {
        $this->arrivalDate = $arrivalDate;

        return $this;
    }

    /**
     * Get arrivalDate
     *
     * @return \DateTime
     */
    public function getArrivalDate()
    {
        return $this->arrivalDate;
    }

    /**
     * Set tripPurpose
     *
     * @param string $tripPurpose
     * @return travel_request
     */
    public function setTripPurpose($tripPurpose)
    {
        $this->tripPurpose = $tripPurpose;

        return $this;
    }

    /**
     * Get tripPurpose
     *
     * @return string
     */
    public function getTripPurpose()
    {
        return $this->tripPurpose;
    }

    /**
     * Set customerRelated
     *
     * @param boolean $customerRelated
     * @return travel_request
     */
    public function setCustomerRelated($customerRelated)
    {
        $this->customerRelated = $customerRelated;

        return $this;
    }

    /**
     * Get customerRelated
     *
     * @return boolean
     */
    public function getCustomerRelated()
    {
        return $this->customerRelated;
    }

    /**
     * Set customerName
     *
     * @param string $customerName
     * @return travel_request
     */
    public function setCustomerName($customerName)
    {
        $this->customerName = $customerName;

        return $this;
    }

    /**
     * Get customerName
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * Add destinations
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TRDestination $destinations
     * @return TravelRequest
     */
    public function addDestination(\Opit\OpitHrm\TravelBundle\Entity\TRDestination $destinations)
    {
        $this->destinations[] = $destinations;
        $destinations->setTravelRequest($this); // synchronously updating inverse side

        return $this;
    }

    /**
     * Remove destinations
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TRDestination $destinations
     */
    public function removeDestination(\Opit\OpitHrm\TravelBundle\Entity\TRDestination $destinations)
    {
        $this->destinations->removeElement($destinations);
    }

    /**
     * Get destinations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * Add accomodations
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TRAccomodation $accomodations
     * @return TravelRequest
     */
    public function addAccomodation(\Opit\OpitHrm\TravelBundle\Entity\TRAccomodation $accomodations)
    {
        $this->accomodations[] = $accomodations;
        $accomodations->setTravelRequest($this); // synchronously updating inverse side

        return $this;
    }

    /**
     * Remove accomodations
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TRAccomodation $accomodations
     */
    public function removeAccomodation(\Opit\OpitHrm\TravelBundle\Entity\TRAccomodation $accomodations)
    {
        $this->accomodations->removeElement($accomodations);
    }

    /**
     * Get accomodations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccomodations()
    {
        return $this->accomodations;
    }

    /**
     * Set user
     *
     * @param \Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface $user
     * @return TravelRequest
     */
    public function setUser(\Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Opit\OpitHrm\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set teamManager
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\User $teamManager
     * @return TravelRequest
     */
    public function setTeamManager(\Opit\OpitHrm\UserBundle\Entity\User $teamManager = null)
    {
        $this->teamManager = $teamManager;

        return $this;
    }

    /**
     * Get teamManager
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\User
     */
    public function getTeamManager()
    {
        return $this->teamManager;
    }

    /**
     * Set generalManager
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\User $generalManager
     * @return TravelRequest
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
    public function getGeneralManager()
    {
        return $this->generalManager;
    }

    /**
     * Set travelRequestId

     * @param string $travelRequestId
     * @return TravelRequest
     */
    public function setTravelRequestId($travelRequestId = null)
    {
        $this->travelRequestId = $travelRequestId;

        return $this;
    }

    /**
     * Get travelRequestId
     *
     * @return string
     */
    public function getTravelRequestId()
    {
        return $this->travelRequestId;
    }

    /**
     * Set travelExpense
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TravelRequest
     */
    public function setTravelExpense(TravelExpense $travelExpense = null)
    {
        $this->travelExpense = $travelExpense;

        return $this;
    }

    /**
     * Get travelExpense
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\TravelExpense
     */
    public function getTravelExpense()
    {
        return $this->travelExpense;
    }

    /**
     * Add states
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests $states
     * @return TravelRequest
     */
    public function addState(StatesTravelRequests $states)
    {
        $states->setTravelRequest($this); // synchronously updating inverse side
        $this->states[] = $states;

        return $this;
    }

    /**
     * Remove states
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests $states
     */
    public function removeState(StatesTravelRequests $states)
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
     * Returns the travel type constant
     *
     * @return string The travel entity type
     */
    public static function getType()
    {
        return self::TYPE;
    }

    /**
     * Returns the travel request pattern for the ID generation
     *
     * @return string The travel entity type
     */
    public static function getIDPattern()
    {
        return self::ID_PATTERN;
    }

    /**
     * Add notifications
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TRNotification $notifications
     * @return TravelRequest
     */
    public function addNotification(\Opit\OpitHrm\TravelBundle\Entity\TRNotification $notifications)
    {
        $this->notifications[] = $notifications;
        $notifications->setTravelRequest($this);

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TRNotification $notifications
     */
    public function removeNotification(\Opit\OpitHrm\TravelBundle\Entity\TRNotification $notifications)
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
}
