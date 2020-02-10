<?php

namespace BackBundle\Form;

use BackBundle\Entity\ZipFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class MassUploadForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('month', 'text')
            ->add('year', 'text')
            ->add('company', 'entity', array(
                'empty_value' => 'Toutes les entreprises',
                'class'    => 'FrontBundle:Company',
                'property' => 'nom',
                'expanded' => false,
                'multiple' => false,
                'required' => false
            ))
            ->add('verified', 'checkbox')
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackBundle\Entity\MassUpload'
        ));
    }

    public function getName()
    {
        return 'fulloffice_massupload_form';
    }
}