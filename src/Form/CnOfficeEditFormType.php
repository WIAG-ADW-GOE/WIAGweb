<?php
namespace App\Form;

use App\Entity\CnOffice;
use App\Entity\Domstift;
use App\Entity\Monastery;

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


class CnOfficeEditFormType extends AbstractType {
    private $router;
    private $em;

    public function __construct(RouterInterface $routerInterface,
                                EntityManagerInterface $em) {
        $this->router = $routerInterface;
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => CnOffice::class,
        ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('office_name', null, [
                'label' => 'Amtsart',
                'attr' => [
                    'size' => 35,
                ],
            ])
            ->add('date_start', null, [
                'label' => 'Beginn',
                'required' => false,
                'attr' => [
                    'size' => 30,
                ],

            ])
            ->add('date_end', null, [
                'label' => 'Ende',
                'required' => false,
                'attr' => [
                    'size' => 30,
                ],
            ])
            ->add('comment', null, [
                'label' => 'Kommentar',
                'required' => false,
                'attr' => [
                    'size' => 40,
                ],
            ])
            ->add('diocese', null, [
                'label' => 'Bistum',
                'required' => false,
                'attr' => [
                    'class' => 'js-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_diocese'),
                    'size' => 30,
                ],
            ])
            // use EntityType::class as an alternative (see symfonycast)
            // drawback: there are 4558 monasteries
            ->add('form_monastery_name', TextType::class, [
                'label' => 'Domstift/Kloster',
                'required' => false,
                'mapped' => true,
                'attr' => [
                    'class' => 'js-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_monastery'),
                    'size' => 40,
                ],
            ])
            ->add('n_monasteries', HiddenType::class)
            ->add('archdeacon_territory', null, [
                'label' => 'Archidiakonat',
                'required' => false,
                'attr' => [
                    'size' => 30,
                ],
            ])
            ->add('btn_save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => [
                    'class' => 'btn btn-warning btn-sm'
                ]
            ])
            ->add('btn_delete', SubmitType::class, [
                'label' => 'Löschen',
                'attr' => [
                    'class' => 'btn btn-danger btn-sm'
                ]
            ])

            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'findMonastery'));

        return $builder;
    }

    public function getDomstiftChoices() {
        $repository = $this->em->getRepository(Domstift::class);
        $list = $repository->findAll();
        $choices = array();
        foreach($list as $domstift) {
            $choices[$domstift->getName()] = $domstift->getGsId();
        }
        return $choices;
    }

    public function findMonastery(FormEvent $event) {
        $office = $event->getData();
        $monastery_name = $office['form_monastery_name'];
        $n = null;
        if ($monastery_name == "") {
            $n = null;
        } else {
            $qcount = $this->em->getRepository(Monastery::Class)
                               ->countByName($monastery_name);
            $n = $qcount['n'];
        }

        $office['n_monasteries'] = $n;
        $event->setData($office);
    }


}
