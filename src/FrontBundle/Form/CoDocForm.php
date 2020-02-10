<?php
namespace FrontBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CoDocForm extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('doc', 'file')
            ->add('name', 'text')
            ->add('companies', 'entity', array(
                'empty_value' => 'Toutes les entreprises',
                'class'    => 'FrontBundle:Company',
                'property' => 'nom',
                'expanded' => false,
                'multiple' => true,
                'required' => false
            ))
            ->add('visibility', 'checkbox', array('required'=>false))
            ->add('toSign', 'checkbox', array('required'=>false));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FrontBundle\Entity\CoDoc'
        ));
    }

    public function getName()
    {
        return 'codoc_form';
    }
}