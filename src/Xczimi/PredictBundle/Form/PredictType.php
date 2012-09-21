<?php
namespace Xczimi\PredictBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class PredictType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('home_goal', 'text', array('required' => false));
        $builder->add('away_goal', 'text', array('required' => false));
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Xczimi\PredictBundle\Document\Predict');
    }

    public function getName()
    {
        return 'predict';
    }
}
