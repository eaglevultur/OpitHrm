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
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\OpitHrm\HiringBundle\Form\JobPositionType;
use Opit\OpitHrm\HiringBundle\Entity\JobPosition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Opit\Component\Utils\Utils;

class JobPositionController extends Controller
{

    /**
     * To add/edit job position in OPIT-HRM
     *
     * @Route("/secured/job/show/{id}", name="OpitOpitHrmHiringBundle_job_position_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"GET", "POST"})
     * @Template()
     * @throws AccessDeniedException
     */
    public function showJobPositionAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $jobPositionId = $request->attributes->get('id');
        $isNewJobPosition = 'new' === $jobPositionId;
        $securityContext = $this->container->get('security.context');
        $isTeamManager = $securityContext->isGranted('ROLE_TEAM_MANAGER');
        $currentUser = $securityContext->getToken()->getUser();
        $isEditable = true;
        $errors = array();
        $externalApplicationFormUrl = '';

        if (!$isTeamManager) {
            throw new AccessDeniedException(
                'Access denied for job position.'
            );
        }

        if ($isNewJobPosition) {
            $jobPosition = new JobPosition();
        } else {
            $entityManager->getFilters()->disable('softdeleteable');
            $jobPosition = $entityManager->getRepository('OpitOpitHrmHiringBundle:JobPosition')->find($jobPositionId);

            if (null === $jobPosition) {
                throw $this->createNotFoundException('Missing job position.');
            }

            $isEditable = ($securityContext->isGranted('ROLE_ADMIN') || $currentUser->getId() === $jobPosition->getCreatedUser()->getId());

            // Only show job position external url when job position is active
            if (true === $jobPosition->getIsActive()) {
                $externalApplicationFormUrl = $this->generateUrl(
                    'OpitOpitHrmHiringBundle_job_application',
                    array('token' => $jobPosition->getExternalToken()),
                    true
                );
            }
        }

        $form = $this->createForm(
            new JobPositionType($isNewJobPosition),
            $jobPosition,
            array('em' => $entityManager)
        );

        if ($request->isMethod("POST")) {
            if (!$isEditable) {
                throw new AccessDeniedException(
                    'Job position can not be modified.'
                );
            }

            $form->handleRequest($request);

            if ($form->isValid()) {
                $entityManager->persist($jobPosition);
                $entityManager->flush();

                if ($isNewJobPosition) {
                    $this->sendJpMessages($jobPosition);
                }

                return $this->redirect($this->generateUrl('OpitOpitHrmHiringBundle_job_position_list'));
            } else {
                $errors = Utils::getErrorMessages($form);
            }
        }

        return $this->render(
            'OpitOpitHrmHiringBundle:JobPosition:showJobPosition.html.twig',
            array(
                'form' => $form->createView(),
                'isNewJobPosition' => $isNewJobPosition,
                'isEditable' => $isEditable,
                'errors' => $errors,
                'externalApplicationFormUrl' => $externalApplicationFormUrl
            )
        );
    }

    /**
     * To list job positions in OPIT-HRM
     *
     * @Route("/secured/job/list", name="OpitOpitHrmHiringBundle_job_position_list")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"GET", "POST"})
     * @Template()
     * @throws AccessDeniedException
     */
    public function listJobPositionAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getFilters()->disable('softdeleteable');
        $securityContext = $this->container->get('security.context');
        $isSearch = $request->request->get('issearch');
        $searchRequests = array();
        $config = $this->container->getParameter('pager_config');
        $maxResults = $config['max_results'];
        $offset = $request->request->get('offset');

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

        $jobPositions = $entityManager->getRepository('OpitOpitHrmHiringBundle:JobPosition')
            ->findAllByFiltersPaginated($pagnationParameters, $searchRequests);

        if ($request->request->get('resetForm') || $isSearch || null !== $offset) {
            $template = 'OpitOpitHrmHiringBundle:JobPosition:_list.html.twig';
        } else {
            $template = 'OpitOpitHrmHiringBundle:JobPosition:list.html.twig';
        }

        return $this->render(
            $template,
            array('jobPositions' => $jobPositions)
        );
    }

    /**
     * To delete job position in OPIT-HRM
     *
     * @Route("/secured/job/delete", name="OpitOpitHrmHiringBundle_job_position_delete")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"POST"})
     * @Template()
     * @throws AccessDeniedException
     */
    public function deleteJobPositionAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        $entityManager = $this->getDoctrine()->getManager();
        $currentUser = $securityContext->getToken()->getUser();
        $ids = $request->request->get('deleteMultiple');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $jobPosition = $entityManager->getRepository('OpitOpitHrmHiringBundle:JobPosition')->find($id);

            if (!$securityContext->isGranted('ROLE_ADMIN') || $currentUser->getId() !== $jobPosition->getCreatedUser()->getId()) {
                throw new AccessDeniedException(
                    'Access denied for job position.'
                );
            }

            $entityManager->remove($jobPosition);
        }

        $entityManager->flush();

        return new JsonResponse('success');
    }

    /**
     * To generate details form for job position
     *
     * @Route("/secured/job/show/details", name="OpitOpitHrmHiringBundle_job_show_details")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"POST"})
     * @Template()
     */
    public function showDetailsAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $jobPositionId = $request->request->get('id');
        $jobPosition = $entityManager->getRepository('OpitOpitHrmHiringBundle:JobPosition')->find($jobPositionId);

        if (null === $jobPosition) {
            throw $this->createNotFoundException('Missing leave request.');
        }

        return $this->render(
            'OpitOpitHrmHiringBundle:JobPosition:showDetails.html.twig',
            array(
                'jobPosition' => $jobPosition
            )
        );
    }

    /**
     * @Route("/secured/job/search", name="OpitOpitHrmHiringBundle_job_search")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Method({"POST"})
     */
    public function jobPositionSearchAction()
    {
        $request = $this->getRequest();
        $jobTitle = $request->request->get('term');
        $jobPositions = $this->getDoctrine()
            ->getRepository('OpitOpitHrmHiringBundle:JobPosition')->findByTitleLike($jobTitle);
        $jps = array();

        foreach ($jobPositions as $jobPosition) {
            $jps[] = array(
                'value' => $jobPosition->getJobTitle(),
                'label' => $jobPosition->getJobTitle(),
                'id' => $jobPosition->getId()
            );
        }

        return new JsonResponse($jps);
    }

    /**
     * Function to send email and notification when creating jp.
     *
     * @param \Opit\OpitHrm\HiringBundle\Entity\JobPosition $jobPosition
     */
    protected function sendJpMessages(JobPosition $jobPosition)
    {
        $templateVars = array();
        $templateVars['jobPosition'] = $jobPosition;
        $applicationName = $this->container->getParameter('application_name');

        $emailManager = $this->get('opit.component.email_manager');
        $emailManager->setRecipient($jobPosition->getHiringManager()->getEmail());
        $emailManager->setSubject(
            '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - Job position created (' . $jobPosition->getJobPositionId() . ')'
        );
        $emailManager->setBodyByTemplate('OpitOpitHrmHiringBundle:Mail:jobPosition.html.twig', $templateVars);
        $emailManager->sendMail();

        $notificationManager = $this->get('opit.manager.job_position_notification_manager');
        $notificationManager->addNewJobPositionNotification($jobPosition);
    }
}
