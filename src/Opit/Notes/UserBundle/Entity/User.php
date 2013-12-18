<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Opit\Notes\TravelBundle\Model\TravelRequestUserInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Description of User
 * Custom user entity to validata against a database
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 *
 * @internal Must be implemented the: User, General Manager and Travel manager requests.
 *
 * @ORM\Table(name="notes_users")
 * @ORM\Entity(repositoryClass="Opit\Notes\UserBundle\Entity\UserRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @UniqueEntity(fields={"username"}, message="The username is already used.")
 * @UniqueEntity(fields={"email"}, message="The email is already used.")
 * @UniqueEntity(fields={"employeeName"}, message="The employeeName is already used.")
 */
class User implements UserInterface, \Serializable, TravelRequestUserInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;
    
    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank(message="The username should not be blank.")
     */
    protected $username;
    
    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank(message="The employeeName should not be blank.")
     */
    protected $employeeName;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message="The password should not be blank.")
     * @Assert\Length(
     *      min = "6",
     *      max = "50",
     *      minMessage = "The password should not be at least {{ limit }} characters length",
     *      maxMessage = "The password should not be longer than {{ limit }} characters length"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank(message="The email should not be blank.")
     * @Assert\Email(message = "The email '{{ value }}' is not a valid email.")
     */
    protected $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\JoinColumn(name="job_title_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="JobTitle")
     */
    protected $jobTitle;

    /**
     * @ORM\ManyToMany(targetEntity="Groups", inversedBy="users")
     * @ORM\JoinTable(name="notes_users_groups")
     */
    protected $groups;
    
    /**
     * User travel requests
     * @ORM\OneToMany(targetEntity="\Opit\Notes\TravelBundle\Entity\TravelRequest", mappedBy="user", cascade={"remove"})
     */
    protected $userTravelRequests;

    /**
     * General manager travel requests
     * @ORM\OneToMany(targetEntity="\Opit\Notes\TravelBundle\Entity\TravelRequest", mappedBy="generalManager", cascade={"remove"})
     */
    protected $gmTravelRequests;

    /**
     * Team manager travel requests
     * @ORM\OneToMany(targetEntity="\Opit\Notes\TravelBundle\Entity\TravelRequest", mappedBy="teamManager", cascade={"remove"})
     */
    protected $tmTravelRequests;
    
    /**
     * User travel expenses
     * @ORM\OneToMany(targetEntity="\Opit\Notes\TravelBundle\Entity\TravelExpense", mappedBy="user", cascade={"remove"})
     */
    protected $userTravelExpenses;

    public function __construct()
    {
        //$this->salt = md5(uniqid(null, true));
        $this->isActive = true;
        $this->groups = new ArrayCollection();
        $this->userTravelRequests = new ArrayCollection();
        $this->gmTravelRequests = new ArrayCollection();
        $this->tmTravelRequests = new ArrayCollection();
        $this->userTravelExpenses = new ArrayCollection();
        $this->setSalt("");
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->isActive;
    }     

    /**
     * @inheritDoc
     */
    public function getEmployeeName()
    {
        return $this->employeeName;
    }
    
    /**
     * @inheritDoc
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @inheritDoc
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return $this->groups->toArray();
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
        ) = unserialize($serialized);
    }
    
    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }
    
    /**
     * Set employee name
     *
     * @param string $employeeName
     * @return User
     */
    public function setEmployeeName($employeeName)
    {
        $this->employeeName = $employeeName;
    
        return $this;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Set job
     *
     * @param string $job
     * @return User
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }
    
    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setRoles($role)
    {
        $this->groups[] = $role;
    
        return $this;
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
     * Add groups
     *
     * @param \Opit\Notes\UserBundle\Entity\Groups $groups
     * @return User
     */
    public function addGroup(\Opit\Notes\UserBundle\Entity\Groups $groups)
    {
        $this->groups[] = $groups;
    
        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Opit\Notes\UserBundle\Entity\Groups $groups
     */
    public function removeGroup(\Opit\Notes\UserBundle\Entity\Groups $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }
    
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }
}