<?php
namespace App\Form;

use App\Form\Model\BishopQueryFormModel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Routing\RouterInterface;


class BishopQueryFormType extends AbstractType
{

    public function __construct(RouterInterface $router) {
        $this->router = $router;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => false,
                'attr' => [
                    'class' => 'js-name-autocomplete',
                    // 'data-autocomplete-url' => $this->router->generate('query_bishops_names_utility'),
                ]
            ])
            ->add('place', TextType::class, [
                'label' => 'Ort',
                'required' => false,
            ])
            ->add('year', NumberType::class, [
                'label' => 'Jahr',
                'required' => false,
            ])
            ->add('someid', TextType::class, [
                'label' => 'Nummer',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BishopQueryFormModel::class,
        ]);
    }
}
