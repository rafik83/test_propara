<?php

namespace FrontBundle\Form;

use BackBundle\Form\PersonnalisationForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('nom', 'text')
                ->add('extra_rh_user', 'hidden', [
                    'mapped' => false,
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'FrontBundle\Entity\Company'
        ));
    }

    public function getName() {
        return 'company_type';
    }

}
