<?php

namespace FrontBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ActivateSalaryForm extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'options' => array('required' => true),
                'first_options'  => array('label' => 'Nouveau mot de passe'),
                'second_options' => array('label' => 'Confirmation du mot de passe'),

            ));

    }


    public function getName()
    {
        return 'user_change_pwd';
    }

}