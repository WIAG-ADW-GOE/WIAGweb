<?php
namespace App\Form;

use App\Entity\User;
use App\Entity\OfficeCount;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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


class UserFormType extends AbstractType
{


    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $options['data'] ?? null;

        $builder
            ->add('email', TextType::class, [
                'label' => 'E-Mail',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Passwort',
                'required' => true,
            ])
            ->add('passwordtwin', PasswordType::class, [
                'label' => 'Passwort Wiederholung',
                'mapped' => false,
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rolle',
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_QUERY' => 'ROLE_QUERY',
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
            ]);
    }

}
