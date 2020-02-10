<?php

namespace BackBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use FrontBundle\Entity\Role;
use FrontBundle\Entity\User;
use FrontBundle\Entity\Company;

class ResponsableEditType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function __construct($em) {
        $this->em = $em;
    }

    public function getAllRoles() {
        $em = $this->em;
        $entity = $em->getRepository('FrontBundle:Role')->findAll();
        $roles = array();
        foreach ($entity as $value) {
            $roles[$value->getId()] = $value->getName();
        }
        return $roles;
    }

    public function getAllCompany() {
        $em = $this->em;
        $entity = $em->getRepository('FrontBundle:Company')->findAll();
        $companies = array();

        foreach ($entity as $value) {
            $companies[$value->getId()] = $value->getNom();
        }
        return $companies;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('libeller', 'text')
                ->add('extra_company', 'hidden', [
                    'mapped' => false,
                ])
                ->add('extra_role', 'hidden', [
                    'mapped' => false,
                ]);
//                ->add('nom', 'entity', array(
//                    'class' => 'FrontBundle:Company',
//                    'property' => 'nom',
//                    'empty_value' => 'Entité',
//                    'multiple' => true,
//                    'data' => $this->getAllCompany()
//        ));
//                ->add('companies', 'entity', array(
//                    'class' => 'FrontBundle:Company',
//                    'property' => 'nom',
//                    'multiple'    => true,
//                    'data' => $this->getAllCompany()
//                ))
//                ->add('role', 'entity', array(
//                    'class' => 'FrontBundle:Role',
//                    'property' => 'name',
//                    'multiple'    => false,
//                    'data' => $this->getAllRoles()
//                ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'FrontBundle\Entity\Responsable'
        ));
    }

    public function getName() {
        return 'company_responsable_form';
    }

}
