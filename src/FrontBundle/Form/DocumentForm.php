<?php
namespace FrontBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DocumentForm extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('doc', 'file')
            ->add('name', 'text')
            ->add('visibility', 'checkbox', array('required'=>false))
            ->add('specialDoc', 'checkbox', array('required'=>false))
            ->add('category', 'entity', array(
                'class'    => 'FrontBundle:Category',
                'property' => 'libelle',
                'expanded' => false,
                'multiple' => false,
                'required' => true
            ))
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FrontBundle\Entity\Document'
        ));
    }

    public function getName()
    {
        return 'document_form';
    }
}