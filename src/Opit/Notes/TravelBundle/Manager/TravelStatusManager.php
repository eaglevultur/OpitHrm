<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Manager;

use Doctrine\ORM\EntityManager;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Notes\TravelBundle\Helper\Utils;
use Opit\Notes\TravelBundle\Entity\Token;
use Opit\Notes\StatusBundle\Manager\StatusManager;

/**
 * Description of TravelStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TravelStatusManager extends StatusManager
{
    protected $entityManager;
    protected $mail;
    protected $factory;
    protected $container;
    
    public function __construct(EntityManager $entityManager, $mail, $factory, $container)
    {
        $this->entityManager = $entityManager;
        $this->mail = $mail;
        $this->factory = $factory;
        $this->container = $container;
    }
    
    /**
     * Removes the tokens to the related travel request or travel expense.
     * 
     * @param integer $id
     */
    public function removeTravelTokens($id)
    {
        $tokens = $this->entityManager->getRepository('OpitNotesTravelBundle:Token')
            ->findBy(array('travelId' => $id));
        foreach ($tokens as $token) {
            $this->entityManager->remove($token);
        }
        $this->entityManager->flush();
    }
    
    /**
     * Method to set token for a travel request or travel expense.
     * 
     * @param integer $id
     */
    public function setTravelToken($id)
    {
        //set token for travel
        $token = new Token();
        // encode token with factory encoder
        $encoder = $this->factory->getEncoder($token);
        $travelToken =
            str_replace('/', '', $encoder->encodePassword(serialize($id) . date('Y-m-d H:i:s'), ''));
        $token->setToken($travelToken);
        $token->setTravelId($id);
        $this->entityManager->persist($token);
        $this->entityManager->flush();
        
        return $travelToken;
    }
    
    /**
     * 
     * @param \Opit\Notes\StatusBundle\Entity\Status $status
     * @param array $nextStates
     * @param mixed $resource
     * @param integer $requiredStatus
     * @return boolean
     */
    protected function prepareEmail(Status $status, array $nextStates, $resource, $requiredStatus)
    {
        // get template name by converting entity name first letter to lower
        $className = Utils::getClassBasename($resource);
        // lowercase first character of string
        $template = lcfirst($className);
        $router = $this->container->get('router');
        $statusName = $status->getName();
        $statusId = $status->getId();
        // split class name at uppercase letters
        $subjectType = preg_split('/(?=[A-Z])/', $className);
        // decide if resource is request or expense, if is expense get its request
        $travelRequest = ($resource instanceof TravelExpense) ? $resource->getTravelRequest() : $resource;
        $generalManager = $travelRequest->getGeneralManager();
        // call method located in travel expense service
        $estimatedCosts = $this->container->get('opit.model.travel_expense')
            ->getTRCosts($travelRequest);
        // create string for email travel type e.g.(Travel expense, Travel request)
        $subjectTravelType = $subjectType[1] . ' ' . strtolower($subjectType[2]);
        $stateChangeLinks = array();
        
        if (Status::FOR_APPROVAL === $statusId) {
            $travelToken = $this->setTravelToken($resource->getId());

            foreach ($nextStates as $key => $value) {
                if ($key !== $requiredStatus) {
                    // Generate links that can be used to change the status of the travel request
                    $stateChangeLinks[] = $router->generate('OpitNotesTravelBundle_change_status', array(
                        'gmId' => $generalManager->getId(),
                        'travelType' => $resource::getType(),
                        'status' => $key,
                        'token' => $travelToken
                    ), true);
                }
            }

            $recipient = $generalManager->getEmail();
            $templateVariables = array(
                'nextStates' => $nextStates,
                'stateChangeLinks' => $stateChangeLinks
            );
        } else {
            $recipient = $travelRequest->getUser()->getEmail();
            $templateVariables = array(
                'currentState' => $statusName,
                'url' => $router->generate('OpitNotesUserBundle_security_login', array(), true)
            );

            switch ($statusId) {
                case Status::APPROVED:
                    $templateVariables['isApproved'] = true;
                    break;
                case Status::REVISE:
                    $templateVariables['isRevised'] = true;
                    break;
                case Status::REJECTED:
                    $templateVariables['isRejected'] = true;
                    break;
                case Status::CREATED:
                    $templateVariables['isCreated'] = true;
                    break;
            }
        }

        // set estimated in HUF and EUR for template
        $templateVariables['estimatedCostsEUR'] = ceil($estimatedCosts['EUR']);
        $templateVariables['estimatedCostsHUF'] = ceil($estimatedCosts['HUF']);
        $templateVariables[$template] = $resource;

        $this->mail->setRecipient($recipient);
        $this->mail->setSubject(
            $subjectTravelType .
            ' (' . $travelRequest->getTravelRequestId() . ') status changed to ' .
            strtolower($statusName)
        );

        $this->mail->setBaseTemplate(
            'OpitNotesTravelBundle:Mail:' . $template . '.html.twig',
            $templateVariables
        );

        $this->mail->sendMail();
    }
}