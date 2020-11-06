<?php
namespace App\Form;

use App\Form\Model\BishopQueryFormModel;
use App\Repository\PersonRepository;
use App\Entity\PlaceCount;
use App\Entity\OfficeCount;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Routing\RouterInterface;


class BishopQueryFormType extends AbstractType
{
    private $router;
    private $personRepository;

    public function __construct(RouterInterface $rtr,
                                PersonRepository $personRepository) {
        $this->router = $rtr;
        $this->personRepository = $personRepository;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => BishopQueryFormModel::class,
        ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $bishopquery = $options['data'] ?? null;

        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => false,
                'attr' => [
                    'class' => 'js-name-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('query_bishops_utility_names'),
                    'size' => '30',
                ],
            ])
            ->add('place', TextType::class, [
                'label' => 'Bistum',
                'required' => false,
                'attr' => [
                    'class' => 'js-place-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('query_bishops_utility_places'),
                    'size' => '15',
                ],
            ])
            ->add('office', TextType::class, [
                'label' => 'Amt',
                'required' => false,
                'attr' => [
                    'class' => 'js-office-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('query_bishops_utility_offices'),
                    'size' => '18',
                ],
            ])
            ->add('year', NumberType::class, [
                'label' => 'Jahr',
                'required' => false,
                'attr' => [
                    'size' => '8',
                ],
            ])->add('someid', TextType::class, [
                'label' => 'Nummer',
                'required' => false,
                'attr' => [
                    'size' => '14',
                ],
            ])->add('searchHTML', SubmitType::class, [
                'label' => 'Suche',
            ])->add('searchJSON', SubmitType::class, [
                'label' => 'JSON',
                'attr' => [
                    'class' => 'btn btn-secondary btn-light',
                ]
            ])->add('searchCSV', SubmitType::class, [
                'label' => 'CSV',
                'attr' => [
                    'class' => 'btn btn-secondary btn-light',
                ]
            ]);


        if($bishopquery && !$bishopquery->isEmpty()) {
            $this->createFacetPlaces($builder, $bishopquery);
            $this->createFacetOffices($builder, $bishopquery);
        }

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            array($this, 'createFacetPlacesByEvent'));


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            array($this, 'createFacetOfficesByEvent'));

    }

    public function createFacetPlacesByEvent(FormEvent $event) {
        $data = $event->getData();
        if (!$data) return;
        if (is_a($data, BishopQueryFormModel::class)) {
            $bishopquery = $data;
        } else {
            $bishopquery = new BishopQueryFormModel();
            $bishopquery->setFieldsByArray($data);
        }

        if ($bishopquery->isEmpty()) return;


        $this->createFacetPlaces($event->getForm(), $bishopquery);

    }

    public function createFacetPlaces($form, $bishopquery) {
        // do not filter by diocese themselves
        $bqsansfacetPlaces = clone $bishopquery;
        $bqsansfacetPlaces->setFacetPlaces(array());

        $places = $this->personRepository->findOfficePlaces($bqsansfacetPlaces);

        $choices = array();

        foreach($places as $place) {
            $choices[] = new PlaceCount($place['diocese'], $place['n']);
        }

        // add selected fields with frequency 0
        $facetPlaces = $bishopquery->getFacetPlacesAsArray();
        if ($facetPlaces) {
            foreach($facetPlaces as $fpl) {
                if (!PlaceCount::find($fpl, $choices)) {
                    $choices[] = new PlaceCount($fpl, '0');
                }
            }
            uasort($choices, array('App\Entity\PlaceCount', 'isless'));
        }

        if ($places) {
            $form->add('facetPlaces', ChoiceType::class, [
                'label' => 'Filter Bistum',
                'expanded' => true,
                'multiple' => true,
                'choices' => $choices,
                'choice_label' => ChoiceList::label($this, 'label'),
                'choice_value' => 'name',
            ]);
        }
    }


    public function createFacetOfficesByEvent(FormEvent $event) {
        $data = $event->getData();
        if (!$data) return;
        if (is_a($data, BishopQueryFormModel::class)) {
            $bishopquery = $data;
        } else {
            $bishopquery = new BishopQueryFormModel();
            $bishopquery->setFieldsByArray($data);
        }

        if ($bishopquery->isEmpty()) return;

        $form = $event->getForm();
        $this->createFacetOffices($form, $bishopquery);

    }

    public function createFacetOffices($form, $bishopquery) {
        // do not filter the database query by offices themselves
        $bqsansfacetOffices = clone $bishopquery;
        $bqsansfacetOffices->setFacetOffices(array());
        $offices = $this->personRepository->findOfficeNames($bqsansfacetOffices);


        $choices = array();
        foreach($offices as $office) {
            $choices[] = new OfficeCount($office['office_name'], $office['n']);
        }

        // add selected fields with frequency 0
        $facetOffices = $bishopquery->getFacetOfficesAsArray();
        if ($facetOffices) {
            foreach($facetOffices as $foc) {
                if (!PlaceCount::find($foc, $choices)) {
                    $choices[] = new OfficeCount($foc, '0');
                }
            }
            uasort($choices, array('App\Entity\OfficeCount', 'isless'));
        }

        if ($offices) {
            $form->add('facetOffices', ChoiceType::class, [
                'label' => 'Filter Amt',
                'expanded' => true,
                'multiple' => true,
                'choices' => $choices,
                'choice_label' => ChoiceList::label($this, 'label'),
                'choice_value' => 'name',
            ]);
        }
    }


}
