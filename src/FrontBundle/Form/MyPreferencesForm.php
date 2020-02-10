<?php
namespace FrontBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MyPreferencesForm extends AbstractType{

    private $options = array();
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder

            ->add('emailPerso','email', array('required'=>true))
            ->add('telephonePerso','text', array('required'=>false));

       if($this->options['choice_salary']) {
           $builder->add('isPaper', 'checkbox', array('required' => false));
               }
        $builder
            ->add('photo', 'file', array('required'=> false, 'data_class' => null))
            ->add('user', new UserEditForm());

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FrontBundle\Entity\Salary'
        ));
    }

    public function getName()
    {
        return 'my_preferences_form';
    }

    public function __construct(array $options)
    {
        $this->options = $options;
    }
}