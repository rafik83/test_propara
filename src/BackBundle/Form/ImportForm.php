<?php

namespace BackBundle\Form;


use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('importFile', 'file', array('required' => false,
                'data_class' => null
            ))
            ->add('separateur', 'choice', array(
                'choices' => array(
                    ';' => '; (point virgule)' ,
                    ',' => ', (virgule)'
                )
            ))
            ->add('formatDate', 'choice', array(
                'choices' => array(
                    'jma' => 'jj/mm/aaaa' ,
                    'mja' => 'mm/jj/aaaa',
                    'amj' => 'aaaa-mm-jj'
                )
            ))
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackBundle\Entity\Import'
        ));
    }

    public function getName()
    {
        return 'multisign_import';
    }
}