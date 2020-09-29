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

    public function __construct(RouterInterface $rtr, PersonRepository $pry) {
        $this->router = $rtr;
        $this->personRepository = $pry;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => BishopQueryFormModel::class,
        ]);
        
    }

    // public function configureOptions(OptionsResolver $resolver) {
    //     $resolver->setDefaults([
    //         'data_class' => BishopQueryFormModel::class,
    //     ]);
    // }

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
                'label' => 'Ort',
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
            ])
            ->add('someid', TextType::class, [
                'label' => 'Nummer',
                'required' => false,
                'attr' => [
                    'size' => '14',
                ],
            ]);

        // $choicespl[] = new PlaceCount('Mainz', '2');
        // $builder->add('facetPlaces', ChoiceType::class, [
        //     'label' => 'Filter nach Orten',
        //     'expanded' => true,
        //     'multiple' => true,
        //     'choices' => $choicespl,
        //     'choice_label' => ChoiceList::label($this, 'label'),
        // ]);

        $builder->get('name')->addEventListener(
            FormEvents::POST_SET_DATA,
            function(FormEvent $event) {
                // dump($event->getData());
                // dump($event->getForm()->getData());
                $form = $event->getForm()->getParent();
                $name = $event->getForm()->getData();
                // if buildForm is called with the last instance of bishopquery
                // all fields of bishopquery are available
                // TODO recover selected fields
                // if everything fails, pass the state of the facet via the controller
                // and set the Checkboxes via JavaScript
                $bishopquery = $form->getData();
                dump($bishopquery);
                if ($name == 'aul') {
                    $choicespl[5] = new PlaceCount('Prag', '12');
                    $choicespl[6] = new PlaceCount('Pedena', '12');
                    $choicespl[7] = new PlaceCount('Köln', '3');
                } else {
                    $choicespl[11] = new PlaceCount('Mainz', '12');
                    $choicespl[12] = new PlaceCount('Worms', '12');
                }
                $form->add('facetPlaces', ChoiceType::class, [
                    'label' => 'Filter nach Orten',
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $choicespl,
                    'choice_label' => ChoiceList::label($this, 'label'),
                    'choice_attr' => function ($choice, $key, $value) {
                        return ['checked' => 'checked'];
                    }
                    // ChoiceList::attr($this, 'attr'),
                ]);                
            });

        $builder->get('name')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) {
                // dump($event->getData());
                // dump($event->getForm()->getData());
                $form = $event->getForm()->getParent();
                $name = $event->getForm()->getData();
                if ($name == 'aul') {
                    $choicespl[] = new PlaceCount('Prag', '12');
                    $choicespl[] = new PlaceCount('Pedena', '12');
                    $choicespl[] = new PlaceCount('Salzburg', '12');
                } else {
                    $choicespl[] = new PlaceCount('Mainz', '12');
                    $choicespl[] = new PlaceCount('Worms', '12');
                }
                $form->add('facetPlaces', ChoiceType::class, [
                    'label' => 'Filter nach Orten',
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $choicespl,
                    'choice_label' => ChoiceList::label($this, 'label'),
                ]);                
            });
        
        
        // $builder->get('facetOffices')->addEventListener(
        //     FormEvents::POST_SUBMIT,
        //     function($event) use ($bishopquery) {
        //         $formicb = $event->getForm();
        //         $parent = $formicb->getParent();
        //         dump($bishopquery);
        //         $parent->remove('facetPlaces');
        //         $choicespl[] = new PlaceCount('Mainz', '2');
        //         $choicespl[] = new PlaceCount('Prag', '12');
        //         $choicespl[] = new PlaceCount('Minden', '6');               
        //         $parent->add('facetPlaces', ChoiceType::class, [
        //             'label' => 'Filter nach Orten',
        //             'expanded' => true,
        //             'multiple' => true,
        //             'choices' => $choicespl,
        //             'choice_label' => ChoiceList::label($this, 'label'),
        //         ]);                
        //     }
        // );
        

        
        // $choicespl[] = new PlaceCount('Mainz', '2');
        // $choicespl[] = new PlaceCount('Prag', '12');
        // $choicespl[] = new PlaceCount('Minden', '6');

        // dump('build facetPlaces');
        // if ($builder->has('facetPlaces'))
        //     dump($builder->get('facetPlaces'));
        // $builder->remove('facetPlaces');
        // $builder->add('facetPlaces', ChoiceType::class, [
        //     'label' => 'Filter nach Orten',
        //     'expanded' => true,
        //     'multiple' => true,
        //     'choices' => $choicespl,
        //     'choice_label' => ChoiceList::label($this, 'label'),
        //     'data' => null,            
        // ]);


        // $builder->addEventListener(
        //     FormEvents::PRE_SUBMIT,
        //     array($this, 'createFacetPlaces'));


        // $builder->addEventListener(
        //     FormEvents::PRE_SUBMIT,
        //     array($this, 'createFacetOffices'));

        

        if (false && $bishopquery) {

            $places = $this->personRepository->findPlacesByQueryObject($bishopquery);

            if ($places) {
            
                $choicespl = array();

                foreach($places as $place) {
                    $choicespl[] = new PlaceCount($place['diocese'], $place['n']);
                }
                
                $builder->add('facetPlaces', ChoiceType::class, [
                    'label' => 'Filter nach Orten',
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $choicespl,
                    'choice_label' => ChoiceList::label($this, 'label'),
                ]);
            }

            
            

            $choicesoc[] = new OfficeCount('Amt P', '2');
            $choicesoc[] = new OfficeCount('Amt Q', '12');

            $builder->add('facetOffices', ChoiceType::class, [
                'label' => 'Filter nach Orten',
                'expanded' => true,
                'multiple' => true,
                'choices' => $choicesoc,
                'choice_label' => ChoiceList::label($this, 'label'),
            ]);
        }
        
    }

    

    public function addFacetPlaces($form) {
        $bishopquery = $form->getData();
        if ($bishopquery->isEmpty()) return;

        $places = $this->personRepository->findPlacesByQueryObject($bishopquery);

        // TODO set up the facet as a collection of checkboxes
        // $formicb = $event->getForm();
        // $ip = 0;
        // foreach ($places as $place) {
        //     $formicb->add("fpl_{$ip}", CheckboxType::class, [
        //         'label' => $place['diocese']." (".$place['n'].")",
        //         'required' => false,
        //     ]);
        //     $ip += 1;
        // }

        $choices = array();
        
        foreach($places as $place) {
            $choices[] = new PlaceCount($place['diocese'], $place['n']);
        }

        if ($places) {
            $form->add('facetPlaces', ChoiceType::class, [
                'label' => 'Filter nach Orten',
                'expanded' => true,
                'multiple' => true,
                'choices' => $choices,
                'choice_label' => ChoiceList::label($this, 'label'),
            ]);
        }
    }

    public function createFacetOffices(FormEvent $event) {
        $data = $event->getData();
        if (!$data) return;

        $facetPlaces = array_key_exists('facetPlaces', $data) ? $data['facetPlaces'] : null;

        $bishopquery = new BishopQueryFormModel($data['name'],
                                                $data['place'],
                                                $data['office'],
                                                $data['year'],
                                                $data['someid'],
                                                $facetPlaces,
                                                null);

        if ($bishopquery->isEmpty()) return;

        $offices = $this->personRepository->findOfficesByQueryObject($bishopquery);

        $choices = array();
        foreach($offices as $office) {
            $choices[] = new OfficeCount($office['office_name'], $office['n']);
        }

        if ($offices) {
            $formicb = $event->getForm();
            $formicb->add('facetOffices', ChoiceType::class, [
                'label' => 'Filter nach Orten',
                'expanded' => true,
                'multiple' => true,
                'choices' => $choices,
                'choice_label' => ChoiceList::label($this, 'label'),
            ]);
        }
    }

    public function getPlaces($name, $place, $office, $year, $someid) {
        $places = array();
        if ($name == 'hohen' and $office == 'vikar') {
            $places_i = [
                'Freising',
                'Gurk',
                'Lavant',
                'Linz',
                'Mainz'];
            $places = array_combine($places_i, $places_i);
        }

        return $places;
    }

}
