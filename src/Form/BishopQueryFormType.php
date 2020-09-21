<?php
namespace App\Form;

use App\Form\Model\BishopQueryFormModel;
use App\Repository\PersonRepository;
use App\Entity\PlaceCount;
use App\Entity\OfficeCount;

use Ds\Vector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

    // public function configureOptions(OptionsResolver $resolver) {
    //     $resolver->setDefaults([
    //         'data_class' => BishopQueryFormModel::class,
    //     ]);
    // }

    public function buildForm(FormBuilderInterface $builder, array $options) {
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
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'createPlacesFacet'));
        // ->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'createOfficesFacet'));
    }

    public function createPlacesFacet(FormEvent $event) {
        $data = $event->getData();

        if (!$data) return;
        // dump($data);

        $bishopquery = new BishopQueryFormModel($data['name'],
                                                $data['place'],
                                                $data['office'],
                                                $data['year'],
                                                $data['someid'],
                                                array(),
                                                array());


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

        $choices = new Vector();
        
        foreach($places as $place) {
            $choices->push(new PlaceCount($place['diocese'], $place['n']));
        }

        if ($places) {
            $formicb = $event->getForm();

            $formicb->add('facetPlaces', ChoiceType::class, [
                'label' => 'Filter nach Orten',
                'expanded' => true,
                'multiple' => true,
                'choices' => $choices,
                'choice_label' => ChoiceList::label($this, 'label'),
            ]);
        }
    }

    public function createOfficesFacet(FormEvent $event) {
        $data = $event->getData();
        if (!$data) return;

        dump($data);
        $bishopquery = clone $data;

        if ($bishopquery->isEmpty()) return;
        // $bishopquery->setFacetOffices(array());

        $offices = $this->personRepository->findOfficesByQueryObject($bishopquery);

        $choices = new Vector();
        foreach($offices as $office) {
            $choices->push(new OfficeCount($office['office_name'], $office['n']));
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

}
