<?php
namespace App\Form;

use App\Form\Model\BishopQueryFormModel;
use App\Repository\PersonRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\ORM\EntityManagerInterface;


class BishopQueryFormType extends AbstractType
{
    private $router;
    private $entityManager;
    private $personRepository;

    public function __construct(RouterInterface $rtr, EntityManagerInterface $emi, PersonRepository $pry) {
        $this->router = $rtr;
        $this->entityManager = $emi;
        $this->personRepository = $pry;
    }
    
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
                    'size' => '25',
                ],
            ])
            ->add('year', NumberType::class, [
                'label' => 'Jahr',
                'required' => false,
            ])
            ->add('someid', TextType::class, [
                'label' => 'Nummer',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'createPlacesFacet'));
    }

    public function createPlacesFacet(FormEvent $event) {
        $data = $event->getData();
        $bishopquery = new BishopQueryFormModel($data['name'], $data['place'], $data['year'], $data['someid']);

        $db = $this->entityManager->getConnection();

        // $sql = "SELECT office.diocese, COUNT(office.diocese) as n FROM person, office WHERE".
        //      $this->personRepository->buildWhere($bishopquery).
        //      " AND person.wiagid = office.wiagid_person".
        //      " GROUP BY office.diocese";

        $sql = "SELECT office.diocese FROM person, office WHERE".
             $this->personRepository->buildWhere($bishopquery).
             " AND person.wiagid = office.wiagid_person".
             " GROUP BY office.diocese";        

        // TODO avoid doubled 'AND person.wiagid = office.wiagid_person.

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $places = $stmt->fetchAll();

        //dd($places);

        $formicb = $event->getForm();

        $formicb->add('placesFacet', ChoiceType::class, [
            'label' => 'Filter nach Orten',
            'expanded' => true,
            'multiple' => true,
            'choices' => $places,
            'choice_label' => function($choice, $key, $value) {
                return $choice;
            },
        ]);

    }


}
