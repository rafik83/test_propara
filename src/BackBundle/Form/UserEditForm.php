<?php
namespace BackBundle\Form;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserEditForm extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',        'text')
            ->add('password',       'password', array('required' => false))
            ->add('roles', 'entity', array(
                'class'    => 'FrontBundle:Role',
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->where('r.role IN (\'ROLE_ADMIN\',\'ROLE_RH\',\'RH_READ\',\'RH_WRITE\',\'RH_CAT\')');
                    },
                'property' => 'name',
                'multiple' => true,
                'expanded' => false
            ))
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FrontBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'user_edit_form';
    }
}