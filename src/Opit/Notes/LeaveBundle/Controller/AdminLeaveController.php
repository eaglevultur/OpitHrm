<?php

/*
 * The MIT License
 *
 * Copyright 2014 OPIT\bota.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMID TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Opit\Notes\LeaveBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\LeaveBundle\Entity\LeaveCategory;
use Opit\Notes\LeaveBundle\Form\LeaveCategoryType;
use Opit\Notes\LeaveBundle\Entity\LeaveDate;
use Opit\Notes\LeaveBundle\Form\LeaveDateType;
use Opit\Notes\LeaveBundle\Entity\LeaveType;
use Opit\Notes\LeaveBundle\Form\LeaveTypeType;
use Opit\Notes\LeaveBundle\Entity\LeaveSetting;
use Opit\Notes\LeaveBundle\Form\LeaveSettingType;
use Opit\Component\Utils\Utils;

/**
 * Description of AdminLeaveController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class AdminLeaveController extends Controller
{
    /**
     * To generate list leave categories
     *
     * @Route("/secured/admin/list/leave/categories", name="OpitNotesLeaveBundle_admin_list_leave_categories")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listLeaveCategoriesAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $leaveCategories = $em->getRepository('OpitNotesLeaveBundle:LeaveCategory')->findAll();
        $numberOfRelations = array();

        foreach ($leaveCategories as $leaveCategory) {
            $leaves = $em->getRepository('OpitNotesLeaveBundle:Leave')->findBy(array('category' => $leaveCategory->getId()));

            if (0 !== count($leaves)) {
                $numberOfRelations[$leaveCategory->getId()] = count($leaves);
            }
        }

        return $this->render(
            'OpitNotesLeaveBundle:Admin:' . ($showList ? '_' : '') . 'listLeaveCategories.html.twig',
            array(
                'leaveCategories' => $leaveCategories,
                'numberOfRelations' => $numberOfRelations
            )
        );
    }

    /**
     * To generate add/edit leave category form
     *
     * @Route("/secured/admin/add/leave/category/{id}", name="OpitNotesLeaveBundle_admin_add_leave_category", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addLeaveCategoryAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $errorMessages = array();
        $result = array('response' => 'error');

        if ($id) {
            $leaveCategory = $this->getLeaveCategory($request->attributes->get('id'));
        } else {
            $leaveCategory = new LeaveCategory();
        }

        $form = $this->createForm(new LeaveCategoryType(), $leaveCategory);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($leaveCategory);
                $em->flush();

                $result['response'] = 'success';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($leaveCategory);

            if (count($errors) > 0) {
                foreach ($errors as $e) {
                    $errorMessages[] = $e->getMessage();
                }
            }
            $result['errorMessage'] = $errorMessages;
        }
        return new JsonResponse(array($result));
    }

    /**
     * To generate show leave category form
     *
     * @Route("/secured/admin/show/leave/category/{id}", name="OpitNotesLeaveBundle_admin_show_leave_category", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @Template()
     */
    public function showLeaveCategoryFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');

        if ($id) {
            $leaveCategory = $this->getLeaveCategory($id);
        } else {
            $leaveCategory = new LeaveCategory();
        }

        $form = $this->createForm(
            new LeaveCategoryType(),
            $leaveCategory
        );

        return $this->render(
            'OpitNotesLeaveBundle:Admin:showLeaveCategoryForm.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * To delete leave categories in Notes
     *
     * @Route("/secured/admin/delete/leave/category", name="OpitNotesLeaveBundle_admin_delete_leave_category")
     * @Method({"POST"})
     */
    public function deleteLeaveCategoryAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-leavecategory');
        $result = array('response' => 'error');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $leaves = $em->getRepository('OpitNotesLeaveBundle:Leave')->findBy(array('category' => $id));

            // If the leave cateogry does not assigned to any leaves.
            if (0 === count($leaves)) {
                $leaveCategory = $this->getLeaveCategory($id);
                $em->remove($leaveCategory);
            }
        }
        $em->flush();
        $result['response'] = 'success';

        return new JsonResponse(array('code' => 200, $result));
    }

    /**
     * Returns a leave category object
     *
     * @param integer $leaveCategoryId
     * @return mixed  leaveCategory object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getLeaveCategory($leaveCategoryId = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $leaveCategoryId) {
            $leaveCategoryId = $request->request->get('id');
        }

        $leaveCategory = $em->getRepository('OpitNotesLeaveBundle:LeaveCategory')->find($leaveCategoryId);

        if (!$leaveCategory) {
            throw $this->createNotFoundException('Missing job title for id "' . $leaveCategoryId . '"');
        }

        return $leaveCategory;
    }

    /**
     * To generate list Administrative Leave/Working Day
     *
     * @Route("/secured/admin/list/leave/dates", name="OpitNotesLeaveBundle_admin_list_leave_dates")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listLeaveDateAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $isSearch = (bool) $request->request->get('issearch', 0);
        $years = $em->getRepository('OpitNotesLeaveBundle:LeaveDate')->findAllYears();
        $types = $em->getRepository('OpitNotesLeaveBundle:LeaveType')->findAll();
        $leaveDates = array();

        // Check it is an ajax call to view the specific year's leave/working dates
        if ($showList || $isSearch) {
            $searchProperties = $request->request->get('search');

             // Get the leave dates of the searched year.
            $leaveDates = $em->getRepository('OpitNotesLeaveBundle:LeaveDate')->findAllFiltered($searchProperties);
        } else {
             // Get the leave dates of the current year.
             $leaveDates = $em->getRepository('OpitNotesLeaveBundle:LeaveDate')->findAllFiltered();
        }

        return $this->render(
            'OpitNotesLeaveBundle:Admin:' . ($showList || $isSearch ? '_' : '') . 'listLeaveDates.html.twig',
            array('years' => $years, 'leaveDates' => $leaveDates, 'types' => $types)
        );
    }

    /**
     * To generate show Administrative Leave/Working Day form
     *
     * @Route("/secured/admin/show/leave/date/{id}", name="OpitNotesLeaveBundle_admin_show_leave_date", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @Template()
     */
    public function showLeaveDateFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');

        if ($id) {
            $leaveDate = $this->getLeaveDate($id);
        } else {
            $leaveDate = new LeaveDate();
        }

        $form = $this->createForm(
            new LeaveDateType(),
            $leaveDate
        );

        return $this->render(
            'OpitNotesLeaveBundle:Admin:showLeaveDateForm.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Returns a leave date object
     *
     * @param integer $leaveDateId
     * @return mixed  leaveDate object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getLeaveDate($leaveDateId = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $leaveDateId) {
            $leaveDateId = $request->request->get('id');
        }

        $leaveDate = $em->getRepository('OpitNotesLeaveBundle:LeaveDate')->find($leaveDateId);

        if (!$leaveDate) {
            throw $this->createNotFoundException('Missing job title for id "' . $leaveDateId . '"');
        }

        return $leaveDate;
    }

    /**
     * To generate add/edit Administrative Leave/Working Day form
     *
     * @Route("/secured/admin/add/leave/date/{id}", name="OpitNotesLeaveBundle_admin_add_leave_date", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addLeaveDateAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $errorMessages = array();
        $result = array('response' => 'error');

        if ($id) {
            $leaveDate = $this->getLeaveDate($request->attributes->get('id'));
        } else {
            $leaveDate = new LeaveDate();
        }

        $form = $this->createForm(new LeaveDateType(), $leaveDate);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($leaveDate);
                $em->flush();

                $result['response'] = 'success';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($leaveDate);

            if (count($errors) > 0) {
                foreach ($errors as $e) {
                    $errorMessages[] = $e->getMessage();
                }
            }
            $result['errorMessage'] = $errorMessages;
        }

        return new JsonResponse(array($result));
    }

    /**
     * To delete Administrative Leave/Working Day in Notes
     *
     * @Route("/secured/admin/delete/leave/date", name="OpitNotesLeaveBundle_admin_delete_leave_date")
     * @Method({"POST"})
     */
    public function deleteLeaveDateAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-leavedate');
        $result = array('response' => 'error');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $leaveDate = $this->getLeaveDate($id);
            $em->remove($leaveDate);
        }
        $em->flush();
        $result['response'] = 'success';

        return new JsonResponse(array('code' => 200, $result));
    }

    /**
     * To generate list Administrative Leave/Working Day types
     *
     * @Route("/secured/admin/list/leave/types", name="OpitNotesLeaveBundle_admin_list_leave_types")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listLeaveTypeAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $leaveTypes = $em->getRepository('OpitNotesLeaveBundle:LeaveType')->findAll();
        $numberOfRelations = array();

        foreach ($leaveTypes as $leaveType) {
            $leaveDates = $em->getRepository('OpitNotesLeaveBundle:LeaveDate')->findBy(array('holidayType' => $leaveType->getId()));

            if (0 !== count($leaveDates)) {
                $numberOfRelations[$leaveType->getId()] = count($leaveDates);
            }
        }

        return $this->render(
            'OpitNotesLeaveBundle:Admin:' . ($showList ? '_' : '') . 'listLeaveTypes.html.twig',
            array(
                'leaveTypes' => $leaveTypes,
                'numberOfRelations' => $numberOfRelations
            )
        );
    }

    /**
     * To generate show Administrative Leave/Working Day type form
     *
     * @Route("/secured/admin/show/leave/type/{id}", name="OpitNotesLeaveBundle_admin_show_leave_type", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @Template()
     */
    public function showLeaveTypeFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');

        if ($id) {
            $leaveType = $this->getLeaveType($id);
        } else {
            $leaveType = new LeaveType();
        }

        $form = $this->createForm(
            new LeaveTypeType(),
            $leaveType
        );

        return $this->render(
            'OpitNotesLeaveBundle:Admin:showLeaveTypeForm.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Returns a Administrative Leave/Working Day type object
     *
     * @param integer $leaveTypeId
     * @return mixed  leaveType object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getLeaveType($leaveTypeId = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $leaveTypeId) {
            $leaveTypeId = $request->request->get('id');
        }

        $leaveType = $em->getRepository('OpitNotesLeaveBundle:LeaveType')->find($leaveTypeId);

        if (!$leaveType) {
            throw $this->createNotFoundException('Missing leave type for id "' . $leaveTypeId . '"');
        }

        return $leaveType;
    }

    /**
     * To generate add/edit Administrative Leave/Working Day type form
     *
     * @Route("/secured/admin/add/leave/type/{id}", name="OpitNotesLeaveBundle_admin_add_leave_type", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addLeaveTypeAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $errorMessages = array();
        $result = array('response' => 'error');

        if ($id) {
            $leaveType = $this->getLeaveType($request->attributes->get('id'));
        } else {
            $leaveType = new LeaveType();
        }

        $form = $this->createForm(new LeaveTypeType(), $leaveType);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($leaveType);
                $em->flush();

                $result['response'] = 'success';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($leaveType);

            if (count($errors) > 0) {
                foreach ($errors as $e) {
                    $errorMessages[] = $e->getMessage();
                }
            }
            $result['errorMessage'] = $errorMessages;
        }

        return new JsonResponse(array($result));
    }

    /**
     * To delete Administrative Leave/Working Day types in Notes
     *
     * @Route("/secured/admin/delete/leave/type", name="OpitNotesLeaveBundle_admin_delete_leave_type")
     * @Method({"POST"})
     */
    public function deleteLeaveTypeAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-leavetype');
        $result = array('response' => 'error');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $leaveDates = $em->getRepository('OpitNotesLeaveBundle:LeaveDate')->findBy(array('holidayType' => $id));

            if (0 === count($leaveDates)) {
                $leaveType = $this->getLeaveType($id);
                $em->remove($leaveType);
            }
        }
        $em->flush();
        $result['response'] = 'success';

        return new JsonResponse(array('code' => 200, $result));
    }

    /**
     * To generate list leave options
     *
     * @Route("/secured/admin/list/leave/options", name="OpitNotesLeaveBundle_admin_list_leave_settings")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listLeaveSettingsAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $config = $this->container->getParameter('opit_notes_leave');
        $isEnabled = $config['leave_entitlement_plan']['enabled'];
        $groupedLeaveSettings = array();
        $leaveGroups = array();

        // If the leave settings configuration is enabled in the config.
        if ($isEnabled) {
            $em = $this->getDoctrine()->getManager();
            $leaveSettings = $em->getRepository('OpitNotesLeaveBundle:LeaveSetting')->findAll();
            $leaveGroups = $em->getRepository('OpitNotesLeaveBundle:LeaveGroup')->findAll();

            // Grouping the leave dates by year
            foreach ($leaveSettings as $setting) {
                $groupedLeaveSettings[$setting->getLeaveGroup()->getName()][] = $setting;
            }
        }

        return $this->render(
            $showList ? 'OpitNotesLeaveBundle:Admin:_listLeaveSettings.html.twig' : 'OpitNotesLeaveBundle:Admin:listLeaveSettings.html.twig',
            array('groupedLeaveSettings' => $groupedLeaveSettings, 'leaveGroups' => $leaveGroups, 'isEnabled' => $isEnabled)
        );
    }

    /**
     * To show leave option
     *
     * @Route("/secured/admin/show/leave/option/{id}", name="OpitNotesLeaveBundle_admin_show_leave_setting", defaults={"id" = "new"}, requirements={ "id" = "\d|new"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function showLeaveSettingAction(Request $request)
    {
        $id = $request->attributes->get('id');

        if ($id == 'new') {
            $index = null;
            $leaveSetting = new LeaveSetting();
        } else {
            $index = $request->attributes->get('index');
            $leaveSetting = $this->getLeaveSetting($id);
        }


        $form = $this->createForm(
            new LeaveSettingType(),
            $leaveSetting
        );
        return $this->render(
            'OpitNotesLeaveBundle:Admin:showLeaveSettingForm.html.twig',
            array('form' => $form->createView(), 'index' => $index)
        );
    }

    /**
     * To save leave setting
     *
     * @Route("/secured/admin/save/leavesetting", name="OpitNotesLeaveBundle_admin_save_leave_setting")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function saveLeaveSettingAction()
    {
        $request = $this->getRequest();
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $result['response'] = 'success';
        $status = null;

        //If it was a post
        if ($request->isMethod('POST')) {

            $leaveSettingList = $em->getRepository('OpitNotesLeaveBundle:LeaveSetting')->findAll();
            $ids = Utils::arrayValueRecursive('id', $data);

            // Remove leave settings
            foreach ($leaveSettingList as $hs) {
                if (!in_array($hs->getId(), $ids)) {
                    // delete
                    $leaveSetting = $this->getLeaveSetting($hs->getId(), false);
                    $em->remove($leaveSetting);
                    $em->flush();
                }
            }
            if (!empty($data)) {
                // Save leave settings
                foreach ($data['leaveSetting'] as $d) {
                    // save
                    $leaveSetting = $this->getLeaveSetting($d['id'], false);
                    $result = $this->setLeaveSettingData($leaveSetting, $d);
                    if (500 === $result['status']) {
                        $status = $result['status'];
                        break;
                    }
                }
            }
        }
        return new JsonResponse(array('code' => $status, $result));
    }

    /**
     * Set the leave setting entity
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveSetting $leaveSetting
     * @param array $data value of leave setting
     * @return int|boolean
     */
    protected function setLeaveSettingData($leaveSetting, $data)
    {
        $em = $this->getDoctrine()->getManager();
        $result = array();
        $result['status'] = 200;

        //If it is a new leave setting create, else modify it.
        if (false === $leaveSetting) {
            // Create a new leave setting and save it.
            $leaveSetting = new LeaveSetting();
        }
        $leaveSetting->setNumber($data['number']);
        $leaveSetting->setNumberOfLeaves($data['numberOfLeaves']);
        // get the leave group entity by id
        $leaveGroup = $em->getRepository('OpitNotesLeaveBundle:LeaveGroup')->find((integer)$data['leaveGroup']);
        $leaveSetting->setLeaveGroup($leaveGroup);

        $validator = $this->get('validator');
        $errors = $validator->validate($leaveSetting);
        // If the validation failed
        if (count($errors) > 0) {
            $result['status'] = 500;
            $result['response'] = 'error';
            // Get the error messages.
            foreach ($errors as $e) {
                $result['errorMessage'][] = $e->getMessage();
            }
        } else {
            $em->persist($leaveSetting);
            $em->flush();
        }
        return $result;
    }

    /**
     * Returns a Leave Option request object
     *
     * @param integer $leaveSettingId
     * @return mixed  LeaveSetting object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getLeaveSetting($leaveSettingId = null, $throwError = true)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $leaveSettingId) {
            $leaveSettingId = $request->request->get('id');
        }

        $leaveSetting = $em->getRepository('OpitNotesLeaveBundle:LeaveSetting')->find($leaveSettingId);

        if (!$leaveSetting || null === $leaveSetting) {
            //If this method throws error or just return with false value.
            if (true === $throwError) {
                throw $this->createNotFoundException('Missing Per diem for id "' . $leaveSetting . '"');
            } else {
                return false;
            }
        }
        return $leaveSetting;
    }
}
