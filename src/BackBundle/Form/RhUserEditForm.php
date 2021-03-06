<?php
namespace BackBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RhUserEditForm extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('nom', 'text')
            ->add('prenom', 'text')
            ->add('email','email', array('required'=>true))
            ->add('user', new UserEditForm());

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FrontBundle\Entity\RhUser'
        ));
    }

    public function getName()
    {
        return 'rh_user_edit_form';
    }
}