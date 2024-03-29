<?php
namespace App\Form;

use App\Entity\Canon;
use App\Entity\Person;
use App\Entity\CnReference;

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


class CanonEditFormType extends AbstractType {
    private $router;
    private $em;

    public function __construct(RouterInterface $routerInterface,
                                EntityManagerInterface $em) {
        $this->router = $routerInterface;
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Canon::class,
        ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $refChoicesRaw = $this->em->getRepository(CnReference::class)
                                  ->findIdAndShorttitle();

        $refChoices = array();
        foreach ($refChoicesRaw as $c) {
            $refChoices[$c['shorttitle']] = $c['id'];
        }

        $builder
            ->add('givenname', null, [
                'label' => 'Vorname',
                'attr' => [
                    'size' => 40,
                ],
            ])
            ->add('familyname', null, [
                'label' => 'Familienname',
                'required' => false,
                'attr' => [
                    'size' => 45,
                ],
            ])
            ->add('prefix_name', null, [
                'label' => 'Präfix',
                'required' => false,
                'attr' => [
                    'size' => 7,
                ],
            ])
            ->add('date_birth', null, [
                'label' => 'geboren',
                'required' => false,
                'attr' => [
                    'size' => 7,
                ],

            ])
            ->add('date_death', null, [
                'label' => 'gestorben',
                'required' => false,
                'attr' => [
                    'size' => 7,
                ],
            ])
            ->add('givenname_variant', null, [
                'label' => 'Vornamenvarianten',
                'required' => false,
                'attr' => [
                    'size' => 40,
                ],

            ])
            ->add('familyname_variant', null, [
                'label' => 'Familiennamenvarianten',
                'required' => false,
                'attr' => [
                    'size' => 40,
                ],
            ])
            ->add('comment_name', null, [
                'label' => 'Kommentar Name',
                'required' => false,
                'attr' => [
                    'size' => 40,
                ],
            ])
            ->add('comment_person', null, [
                'label' => 'Kommentar Person',
                'required' => false,
                'attr' => [
                    'size' => 40,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'expanded' => false,
                'choices' => [
                    'test' => 'test',
                    'xx' => 'xx',
                    'xxC' => 'xxC',
                    'xxJ' => 'xxJ',
                    'xxN' => 'xxN',
                    'importiert' => 'importiert',
                    'merged' => 'merged',
                    'merged_hersche' => 'merged_hersche',
                    'zurückgestellt' => 'zurückgestellt',
                    'Dublette' => 'Dublette',
                    'deleted' => 'deleted',
                    'fertig' => 'fertig',
                    'online' => 'online',
                ]
            ])
            -> add('religious_order', null, [
                'label' => 'Orden',
                'required' => false,
                'attr' => [
                    'size' => 7,
                ],
            ])
            -> add('academic_title', null, [
                'label' => 'akad. Titel',
                'required' => false,
                'attr' => [
                    'size' => 10,
                ],
            ])
            -> add('annotation_ed', null, [
                'label' => 'redaktionelle Bemerkung',
                'required' => false,
                'attr' => [
                    'size' => 40,
                ],
            ])
            -> add('wikipedia_url', TextType::class, [
                'label' => 'Wikipedia URL',
                'required' => false,
                'attr' => [
                    'size' => 50,
                ],

            ])
            -> add('gsn_id', null, [
                'label' => 'GS-Nummer',
                'required' => false,
                'attr' => [
                    'class' => 'js-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_gsn'),
                    'size' => 15,
                ],
            ])
            -> add('gnd_id', null, [
                'label' => 'GND ID',
                'required' => false,
                'attr' => ['size' => 8],
            ])
            -> add('viaf_id', null, [
                'label' => 'VIAF ID',
                'required' => false,
                'attr' => ['size' => 12],
            ])
            -> add('wikidata_id', null, [
                'label' => 'Wikidata ID',
                'required' => false,
                'attr' => ['size' => 12],
            ])
            -> add('wiag_episc_id', null, [
                'label' => 'WIAG Bischof ID',
                'required' => false,
                'attr' => [
                    'class' => 'js-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_episcid'),
                    'size' => 20,
                ],
            ])
            ->add('mergedInto', null, [
                'label' => 'verweist auf',
                'error_bubbling' => true,
                'required' => false,
                'attr' => [
                    'class' => 'js-autocomplete',
                    'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_merged'),
                    'size' => 15,
                ],
            ])
            // ->add('form_reference_name', TextType::class, [
            //     'label' => 'Referenzwerk',
            //     'required' => true,
            //     'attr' => [
            //         'class' => 'js-autocomplete',
            //         'data-autocomplete-url' => $this->router->generate('canon_edit_autocomplete_reference'),
            //         'size' => 50],
            // ])
            ->add('idReference', ChoiceType::class, [
                'label' => 'Referenzwerk',
                'expanded' => false,
                'choices' => $refChoices,
            ])
            ->add('pageReference', null, [
                'label' => 'Seite',
                'required' => false,
                'attr' => ['size' => 8],
            ])
            ->add('idInReference', null, [
                'label' => 'ID/Nr.',
                'required' => false,
                'attr' => ['size' => 15],
            ]);

        return $builder;
    }

}
