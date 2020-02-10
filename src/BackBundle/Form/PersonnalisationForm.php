<?php

namespace BackBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonnalisationForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('couleur', 'text', array('required' => false))
                ->add('emailActivation', 'ckeditor', array('required' => false))
                ->add('emailAfterActivation', 'ckeditor', array('required' => false))
                ->add('emailForgotPassword', 'ckeditor', array('required' => false))
                ->add('emailDocSign', 'ckeditor', array('required' => false))
                ->add('emailBulletin', 'ckeditor', array('required' => false))
                ->add('emailDoc', 'ckeditor', array('required' => false))
                ->add('objMailActivation', 'text', array('required' => false))
                ->add('objMailAfterActivation', 'text', array('required' => false))
                ->add('objMailBulletin', 'text', array('required' => false))
                ->add('objMailPwd', 'text', array('required' => false))
                ->add('objMailDocSign', 'text', array('required' => false))
                ->add('objMailDoc', 'text', array('required' => false))
                ->add('logo', 'file', array('required' => false,
                    'data_class' => null
                ))
                ->add('objMailRelance', 'text', array('required' => false))
                ->add('emailsNotification', 'text', array('required' => false))
                ->add('objMaiLObseletDoc', 'text', array('required' => false))
                ->add('emailObseletDoc', 'ckeditor', array('required' => false))
                ->add('emailRelance', 'ckeditor', array('required' => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BackBundle\Entity\Personnalisation'
        ));
    }

    public function getName() {
        return 'multisign_personnalisation';
    }

}
