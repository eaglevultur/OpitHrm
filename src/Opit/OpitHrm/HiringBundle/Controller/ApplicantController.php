<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\OpitHrm\HiringBundle\Entity\Applicant;
use Opit\OpitHrm\HiringBundle\Form\ApplicantType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\Component\Utils\Utils;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Symfony\Component\Form\FormError;

class ApplicantController extends Controller
{

    /**
     * To add/edit applicant in OPIT-HRM
     *
     * @Route("/secured/applicant/show/{id}", name="OpitOpitHrmHiringBundle_applicant_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"GET", "POST"})
     * @Template()
     * @throws AccessDeniedException
     */
    public function showApplicantAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $applicantId = $request->attributes->get('id');
        $jobPositionId = $request->query->get('jobPositionId');
        $isNewApplicant = 'new' === $applicantId;
        $securityContext = $this->container->get('security.context');
        $isTeamManager = $securityContext->isGranted('ROLE_TEAM_MANAGER');
        $statusManager = $this->get('opit.manager.applicant_status_manager');
        $entityManager->getFilters()->disable('softdeleteable');
        $currentUser = $securityContext->getToken()->getUser();
        $isEditable = true;
        $isStatusLocked = false;
        $errors = array();
        $nextStates = array();
        $applicantCV = '';

        if (!$isTeamManager) {
            throw new AccessDeniedException(
            'Access denied for applicant.'
            );
        }

        if ($isNewApplicant) {
            $applicant = new Applicant();
            // If the job position id exists fetch the job position entity and adding to the applicant.
            // The calling was from the job position list page.
            if (null !== $jobPositionId) {
                $jobPosition = $entityManager->getRepository('OpitOpitHrmHiringBundle:JobPosition')->find($jobPositionId);
                $applicant->setJobPosition($jobPosition);
            }
        } else {
            $applicant = $entityManager->getRepository('OpitOpitHrmHiringBundle:Applicant')->find($applicantId);
            $applicantCV = $applicant->getCv();
            $isEditable = (
                ($securityContext->isGranted('ROLE_ADMIN') || ($applicant->getCreatedUser() &&  $applicant->getCreatedUser()->getId() === $currentUser->getId())) &&
                null === $applicant->getJobPosition()->getDeletedAt()
            );

            if (null === $applicant) {
                throw $this->createNotFoundException('Missing applicant.');
            }
        }

        $currentStatus = $statusManager->getCurrentStatusMetaData($applicant);
        if (null === $currentStatus) {
            $currentStatus = $entityManager->getRepository('OpitOpitHrmStatusBundle:Status')->find(Status::CREATED);
            $isStatusLocked = true;
        } else {
            $currentStatus = $statusManager->getCurrentStatus($applicant);
            $nextStates = $statusManager->getNextStates($currentStatus);
            $isStatusFinalized = Status::HIRED === $currentStatus->getId() || Status::REJECTED === $currentStatus->getId();
            $isEditable = $isStatusFinalized ? false : $isEditable;
            $isStatusLocked = $isStatusFinalized ? true : $isStatusLocked;
        }

        $form = $this->createForm(
            new ApplicantType($isNewApplicant), $applicant, array('em' => $entityManager)
        );

        if ($request->isMethod('POST')) {
            if (!$isEditable) {
                throw new AccessDeniedException(
                'Applicant can not be modified.'
                );
            }

            $form->handleRequest($request);

            if ($form->isValid()) {
                // If new applicant is being added
                // check if applicant has already been added to jp with same email or phone number.
                // Check after for is valid to make sure data is present.
                if ($isNewApplicant && $entityManager->getRepository('OpitOpitHrmHiringBundle:Applicant')->findByEmailPhoneNumber($applicant) > 0) {
                    $form->addError(new FormError(
                        'Email or phone number has been already registered for this job position.'
                    ));
                    $errors = Utils::getErrorMessages($form);
                } else {
                    $entityManager->persist($applicant);
                    $entityManager->flush();

                    if ($isNewApplicant) {
                        $statusManager->addStatus($applicant, Status::CREATED, null);
                    }

                    return $this->redirect($this->generateUrl(
                        null !== $jobPositionId ? 'OpitOpitHrmHiringBundle_job_position_list' : 'OpitOpitHrmHiringBundle_applicant_list'
                    ));
                }
            } else {
                $errors = Utils::getErrorMessages($form);
            }
        }

        return $this->render(
            'OpitOpitHrmHiringBundle:Applicant:showApplicant.html.twig',
            array(
                'form' => $form->createView(),
                'isNewApplicant' => $isNewApplicant,
                'isEditable' => $isEditable,
                'errors' => $errors,
                'isStatusLocked' => $isStatusLocked,
                'nextStates' => $nextStates,
                'currentStatus' => $currentStatus,
                'applicantId' => $applicantId,
                'applicantCV' => $applicantCV,
                'jobPositionId' => $jobPositionId,
            )
        );
    }

    /**
     * To list applicant in OPIT-HRM
     *
     * @Route("/secured/applicant/list", name="OpitOpitHrmHiringBundle_applicant_list")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"GET", "POST"})
     * @Template()
     * @throws AccessDeniedException
     */
    public function listApplicantAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getFilters()->disable('softdeleteable');
        $securityContext = $this->container->get('security.context');
        $isSearch = $request->request->get('issearch');
        $searchRequests = array();
        $config = $this->container->getParameter('pager_config');
        $maxResults = $config['max_results'];
        $offset = $request->request->get('offset');
        $statusManager = $this->get('opit.manager.applicant_status_manager');
        $availableStates = array();
        $isStatusFinalized = array();

        if (!$securityContext->isGranted('ROLE_TEAM_MANAGER')) {
            throw new AccessDeniedException(
            'Access denied for job position listing.'
            );
        }

        if ($isSearch) {
            $searchRequests = $request->request->all();
        }

        $pagnationParameters = array(
            'firstResult' => ($offset * $maxResults),
            'maxResults' => $maxResults
        );

        $applicants = $entityManager->getRepository('OpitOpitHrmHiringBundle:Applicant')
            ->findAllByFiltersPaginated($pagnationParameters, $searchRequests);

        foreach ($applicants as $applicant) {
            $currentStatus = $statusManager->getCurrentStatus($applicant);
            $isStatusFinalized[$applicant->getId()] = (Status::HIRED === $currentStatus->getId() || Status::REJECTED === $currentStatus->getId());
            $availableStates[$applicant->getId()] = $statusManager->getNextStates($currentStatus);
        }

        if ($request->request->get('resetForm') || $isSearch || null !== $offset) {
            $template = 'OpitOpitHrmHiringBundle:Applicant:_list.html.twig';
        } else {
            $template = 'OpitOpitHrmHiringBundle:Applicant:list.html.twig';
        }

        return $this->render(
            $template, array(
                'applicants' => $applicants,
                'availableStates' => $availableStates,
                'isStatusFinalized' => $isStatusFinalized,
            )
        );
    }

    /**
     * To delete applicant in OPIT-HRM
     *
     * @Route("/secured/applicant/delete", name="OpitOpitHrmHiringBundle_applicant_delete")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"POST"})
     * @Template()
     * @throws AccessDeniedException
     */
    public function deleteApplicantAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        $entityManager = $this->getDoctrine()->getManager();
        $currentUser = $securityContext->getToken()->getUser();
        $ids = $request->request->get('deleteMultiple');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $applicant = $entityManager->getRepository('OpitOpitHrmHiringBundle:Applicant')->find($id);
            // If user is admin or applicant has no created user or created use is current user
            if ($securityContext->isGranted('ROLE_ADMIN') || null === $applicant->getCreatedUser() || $currentUser->getId() === $applicant->getCreatedUser()->getId()) {
                $cv = $applicant->getAbsolutePath();
                if (file_exists($cv)) {
                    unlink($cv);
                }
                $entityManager->remove($applicant);
            } else {
                throw new AccessDeniedException(
                    'Access denied for applicant.'
                );
            }
        }
        $entityManager->flush();

        return new JsonResponse('success');
    }

    /**
     * To download applicant cv in OPIT-HRM
     *
     * @Route("/secured/applicant/cv/download/{id}", name="OpitOpitHrmHiringBundle_applicant_cv_download", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Template()
     */
    public function applicantCVDownloadAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $applicant = $entityManager->getRepository('OpitOpitHrmHiringBundle:Applicant')
            ->find($request->attributes->get('id'));

        $CV = $applicant->getAbsolutePath();
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($CV));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($CV) . '";');
        $response->headers->set('Content-length', filesize($CV));
        $response->sendHeaders();
        $response->setContent(readfile($CV));

        return $response;
    }

    /**
     * Method to change state of applicant
     *
     * @Route("/secured/applicant/state/change", name="OpitOpitHrmHiringBundle_applicant_state")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"POST"})
     * @Template()
     */
    public function changeApplicantStateAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->get('status');
        $statusId = $data['id'];
        $applicantId = $data['foreignId'];
        $applicant = $entityManager->getRepository('OpitOpitHrmHiringBundle:Applicant')->find($applicantId);
        $numberOfPositions = $applicant->getJobPosition()->getNumberOfPositions();
        $hiredApplicants =
            $entityManager->getRepository('OpitOpitHrmHiringBundle:Applicant')->findHiredApplicantCount($applicant->getJobPosition()->getId());

        if ($hiredApplicants >= $numberOfPositions && Status::HIRED == $statusId) {
            return new JsonResponse(
                array(
                    'id' => $applicantId,
                    'error' => 'Job position filled. No more applicants can be hired for this job position'
                ),
                500
            );
        }

        // Set comment content or null
        $comment = isset($data['comment']) && $data['comment'] ? $data['comment'] : null;

        $status = $this->get('opit.manager.applicant_status_manager')
            ->addStatus($applicant, $statusId, $comment);

        $this->get('opit.manager.applicant_notification_manager')
            ->addNewApplicantNotification($applicant, $status);

        $nextStates = $this->get('opit.manager.applicant_status_manager')->getNextStates($status);
        $ns = array();

        // Move next states to different array and change key to string so it
        // does not get ordered on the client side
        foreach($nextStates as $key => $value) {
            $ns[$value] = "$key";
        }

        return new JsonResponse(
            array(
                'applicant' => $applicantId,
                'nextStates' => $ns
            )
        );
    }

    /**
     * Retrieves and displays applicant status history
     *
     * @Route("/secured/applicant/states/history/{id}", name="OpitOpitHrmHiringBundle_status_history", requirements={"id"="\d+"})
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"POST"})
     * @Template()
     */
    public function showStatusHistoryAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $applicant = $entityManager
            ->getRepository('OpitOpitHrmHiringBundle:Applicant')
            ->find($id);

        $applicantStates = $entityManager
            ->getRepository('OpitOpitHrmHiringBundle:StatesApplicants')
            ->findByApplicant($applicant, array('created' => 'DESC'));

        return $this->render(
            'OpitOpitHrmCoreBundle:Shared:statusHistory.html.twig',
            array(
                'elements' => array(
                    'tr' => array(
                        'title' => 'Applicant history',
                        'collection' => $applicantStates
                    )
                )
            )
        );
    }

}
