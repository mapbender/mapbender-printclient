<?php
namespace Mapbender\PrintClientBundle\Element;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Mapbender\PrintClientBundle\Form\EventListener\PrintClientSubscriber;
use Mapbender\ManagerBundle\Form\Type\YAMLConfigurationType;

/**
 * 
 */
class PrintClientAdminType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'printclient';
    }

    /**
     * @inheritdoc
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'application' => null
        ));
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subscriber = new PrintClientSubscriber($builder->getFormFactory(),
            $options["application"]);
        $builder->addEventSubscriber($subscriber);
        $builder->add('target', 'target_element',
                array(
                'element_class' => 'Mapbender\\CoreBundle\\Element\\Map',
                'application' => $options['application'],
                'property_path' => '[target]',
                'required' => false))
            ->add('autoOpen', 'checkbox',array('required' => false))
            ->add('scales', 'text', array('required' => false))
            ->add('file_prefix', 'text', array('required' => false))
            ->add('rotatable', 'checkbox',array('required' => false))
            ->add('legend', 'checkbox',array('required' => false))
            ->add('optional_fields', new YAMLConfigurationType(), array('required' => false,'attr' => array('class' => 'code-yaml')))
            ->add('replace_pattern', new YAMLConfigurationType(),array('required' => false,'attr' => array('class' => 'code-yaml')));
    }

}
