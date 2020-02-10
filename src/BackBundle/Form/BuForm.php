<?php

namespace BackBundle\Form;

use BackBundle\Entity\ZipFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class BuForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder

            ->add('month', 'text')
            ->add('year', 'text')
            ->add('bulletin', 'file')
            ->add('ext', 'text')
            ->add('salary', 'entity', array(
                'class'    => 'FrontBundle:Salary',
                'property' => 'fullName',
                'expanded' => false,
                'multiple' => false,
                'required' => true
            ))
        ;
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackBundle\Entity\Bu'
        ));
    }

    public function getName()
    {
        return 'add_bu_form';
    }
}