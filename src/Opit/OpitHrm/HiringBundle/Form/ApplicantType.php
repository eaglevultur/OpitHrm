<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Opit\OpitHrm\HiringBundle\Form\DataTransformer\JobPositionIdToObjectTransformer;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Description of ApplicantType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class ApplicantType extends AbstractType
{
    protected $isNewApplicant;

    public function __construct($isNewApplicant)
    {
        $this->isNewApplicant = $isNewApplicant;
    }

    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['em'];
        $transformer = new JobPositionIdToObjectTransformer($entityManager);

        $builder->add('name', 'text', array(
            'label' => 'Name',
            'required' => true,
            'attr' => array(
                'placeholder' => 'Name',
                'class' => 'width-200'
            )
        ));

        $builder->add('email', 'text', array(
            'label' => 'Email',
            'required' => true,
            'attr' => array(
                'placeholder' => 'Email',
                'class' => 'width-200'
            )
        ));

        $builder->add('keywords', 'text', array(
            'label' => 'Keywords',
            'required' => true,
            'attr' => array(
                'placeholder' => 'e.g. keyword, keyword, keyword',
                'class' => 'width-200'
            )
        ));

        $builder->add('phoneNumber', 'text', array(
            'label' => 'Phone number',
            'required' => true,
            'attr' => array(
                'placeholder' => 'Phone number',
                'class' => 'width-200'
            )
        ));

        $builder->add('applicationDate', 'date', array(
            'widget' => 'single_text',
            'label'=>'Application date',
            'attr' => array(
                'placeholder' => 'Application date',
                'class' => 'width-200'
            )
        ));

        $builder->add(
            $builder->create('jobPosition', 'hidden', array(
                'constraints' => array(
                    new NotBlank(array('message' => 'Job position can not be empty.'))
                )
            ))->addModelTransformer($transformer)
        );

        $jobPosition = $options['data']->getJobPosition();

        $builder->add('jobPositionAc', 'text', array(
            'label' => 'Job position',
            'data' => ($jobPosition ? $jobPosition->getJobTitle() : null),
            'mapped' => false,
            'attr' => array(
                'placeholder' => 'Job position',
                'class' => 'width-200'
            )
        ));

        $builder->add('cvFile', 'file',array(
                'required' => $this->isNewApplicant,
                'label' => 'Upload applicant CV (.doc, .docx, .pdf)'
            )
        );

        $builder->add('create_applicant', 'submit', array(
            'label' => $this->isNewApplicant ? 'Add applicant' : 'Edit applicant',
            'attr' => array('class' => 'button')
        ));
    }

    /**
     * Sets the default form options
     *
     * @param object $resolver An OptionsResolver interface object
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Opit\OpitHrm\HiringBundle\Entity\Applicant'
            ))
            ->setRequired(array(
                'em',
            ))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }

    /**
     * Get the name
     *
     * @return string name
     */
    public function getName()
    {
        return 'applicant';
    }

}
