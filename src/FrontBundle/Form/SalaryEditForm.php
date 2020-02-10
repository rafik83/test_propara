<?php

namespace FrontBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalaryEditForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('nom', 'text')
//                ->add('nom', 'text', array('attr' => array('class' => 'validate[required, minSize[2]] form-control'),'required' => false))
                ->add('prenom', 'text', array('attr' => array('class' => 'validate[required], minSize[2]] form-control')))
                ->add('poste', 'text', array('required' => false))
                ->add('natureContrat', 'text', array('required' => false))
                ->add('company', 'entity', array(
                    'class' => 'FrontBundle:Company',
                    'property' => 'nom',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
//                    'attr' => array('class' => 'validate[required,funcCall[myValidationFunction]] form-control'),
                    'attr' => array('class' => 'validate[required] form-control')
                ))
                ->add('emailPerso', 'text')
//                ->add('emailPerso', 'email', array('required' => true))
                ->add('emailPro', 'email', array('required' => false))
                ->add('telephonePerso', 'text', array('required' => false))
                ->add('telephonePro', 'text', array('required' => false))
                ->add('dateBegin', 'date', array(
                    'required' => true,
                    'years' => range(1970, date('Y'))
                ))
                ->add('dateEnd', 'date', array(
                    'required' => false,
                    'years' => range(2050, 1970)
                ))
                ->add('adresse', 'text', array('required' => false))
                ->add('ville', 'text', array('required' => false))
                ->add('zipcode', 'text', array('required' => false))
                ->add('numSecu', 'text', array('required' => true))
                ->add('birthDay', 'birthday', array('required' => true))
                ->add('matricule', 'text', array('required' => true))
                ->add('isPaper', 'checkbox', array('required' => false))
                ->add('activationSended', 'checkbox', array('required' => false))
                ->add('photo', 'file', array('required' => false, 'data_class' => null))
                ->add('extra_user_name_edit', 'hidden', [
                    'mapped' => false,
                ])
                ->add('extra_user_pwd_edit', 'hidden', [
                    'mapped' => false,
                ])
                ->add('extra_company_salary_edit', 'hidden', [
                    'mapped' => false,
        ]);
        //->add('user', new UserEditForm());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'FrontBundle\Entity\Salary'
        ));
    }

    public function getName() {
        return 'salary_edit_form';
    }

}
