<?php
namespace App\Form;

use App\Form\Model\CanonFormModel;
use App\Repository\CanonRepository;
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


class CanonFormType extends AbstractType
{
    private $router;
    private $repository;

    public function __construct(RouterInterface $routerInterface,
                                CanonRepository $repository) {
        $this->router = $routerInterface;
        $this->repository = $repository;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => CanonFormModel::class,
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
                    'data-autocomplete-url' => $this->router->generate('canon_autocomplete_names'),
                    'size' => '30',
                ],
            ])
            ->add('place', TextType::class, [
                'label' => 'Erzbistum/Bistum',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Erzbistum/Bistum',
                    'class' => 'js-place-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_autocomplete_places'),
                    'size' => '15',
                ],
            ])
            ->add('office', TextType::class, [
                'label' => 'Amt',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Amtsbezeichnung',
                    'class' => 'js-office-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_autocomplete_offices'),
                    'size' => '18',
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
            ])
            ->add('searchJSON', SubmitType::class, [
                'label' => 'JSON',
                'attr' => [
                    'class' => 'btn btn-light btn-sm',
                ]
            ])
            ->add('searchCSV', SubmitType::class, [
                'label' => 'CSV',
                'attr' => [
                    'class' => 'btn btn-light btn-sm',
                ]
            ])
            ->add('searchRDF', SubmitType::class, [
                'label' => 'RDF-XML',
                'attr' => [
                    'class' => 'btn btn-light btn-sm',
                ]
            ])
            ->add('searchJSONLD', SubmitType::class, [
                'label' => 'JSON-LD',
                'attr' => [
                    'class' => 'btn btn-light btn-sm',
                ]
            ]);

        if($canon && !$canon->isEmpty()) {
            $this->createFacetPlaces($builder, $canon);
            $this->createFacetOffices($builder, $canon);
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
        if (is_a($data, CanonFormModel::class)) {
            $canon = $data;
        } else {
            $canon = new CanonFormModel();
            $canon->setFieldsByArray($data);
        }

        if ($canon->isEmpty()) return;


        $this->createFacetPlaces($event->getForm(), $canon);

    }

    public function createFacetPlaces($form, $canon) {
        // do not filter by diocese themselves
        $bqsansfacetPlaces = clone $canon;
        $bqsansfacetPlaces->setFacetPlaces(array());

        $places = $this->repository->findOfficePlaces($bqsansfacetPlaces);

        $choices = array();

        foreach($places as $place) {
            $choices[] = new PlaceCount($place['diocese'], $place['n']);
        }

        // add selected fields with frequency 0
        $facetPlaces = $canon->getFacetPlacesAsArray();
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
        if (is_a($data, CanonFormModel::class)) {
            $canon = $data;
        } else {
            $canon = new CanonFormModel();
            $canon->setFieldsByArray($data);
        }

        if ($canon->isEmpty()) return;

        $form = $event->getForm();
        $this->createFacetOffices($form, $canon);

    }

    public function createFacetOffices($form, $canon) {
        // do not filter the database query by offices themselves
        $bqsansfacetOffices = clone $canon;
        $bqsansfacetOffices->setFacetOffices(array());
        $offices = $this->repository->findOfficeNames($bqsansfacetOffices);


        $choices = array();
        foreach($offices as $office) {
            $choices[] = new OfficeCount($office['officeName'], $office['n']);
        }

        // add selected fields with frequency 0
        $facetOffices = $canon->getFacetOfficesAsArray();
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
