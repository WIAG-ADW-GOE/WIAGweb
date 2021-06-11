<?php
namespace App\Form;

use App\Entity\Canon;
use App\Form\Model\CanonEditSearchFormModel;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Routing\RouterInterface;


class CanonEditSearchFormType extends AbstractType
{
    private $router;
    private $em;

    public function __construct(RouterInterface $routerInterface,
                                EntityManagerInterface $em) {
        $this->router = $routerInterface;
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => CanonEditSearchFormModel::class,
        ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $canon = $options['data'] ?? null;

        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Vor- oder Nachname',
                    'class' => 'js-name-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_name'),
                    'size' => '30',
                ],
            ])
            ->add('monastery', TextType::class, [
                'label' => 'Domstift',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Domstift',
                    'class' => 'js-domstift-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_domstift'),
                    'size' => '8',
                ],
            ])
            ->add('office', TextType::class, [
                'label' => 'Amt',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Amtsbezeichnung',
                    'class' => 'js-office-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_office'),
                    'size' => '18',
                ],
            ])
            ->add('place', TextType::class, [
                'label' => 'Ort',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ort',
                    'class' => 'js-place-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_place'),
                    'size' => '12',
                ],
            ])

            ->add('year', NumberType::class, [
                'label' => 'Jahr',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Jahreszahl',
                    'size' => '8',
                ],
            ])
            ->add('someid', TextType::class, [
                'label' => 'Nummer',
                'required' => false,
                'attr' => [
                    'placeholder' => 'GSN, GND, Wikidata, VIAF',
                    'size' => '25',
                ],
            ])
            ->add('searchHTML', SubmitType::class, [
                'label' => 'Suche',
                'attr' => [
                    'class' => 'btn btn-light btn-sm'
                ]
            ]);


        $astatus = $this->em->getRepository(Canon::class)
                           ->findStatus();
        $choices = array();
        foreach ($astatus as $status) {
            $value = $status['status'];
            $choices[$value] = $value;
        }

        if ($choices) {
            $builder->add('filterStatus', ChoiceType::class, [
                'label' => 'Filter Status',
                'expanded' => true,
                'multiple' => true,
                'choices' => $choices,
                // 'choice_label' => ChoiceList::label($this, 'label'),
                // 'choice_value' => ChoiceList::value($this, 'value'),
            ]);
        }

        return $builder;
    }


}
