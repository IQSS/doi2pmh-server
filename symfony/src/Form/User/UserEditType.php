<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserEditType extends AbstractType
{

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', PasswordType::class,
            [
                'label' => $this->translator->trans('admin.form.user.password.old.label'),
                'mapped' => false
            ])
            ->add('plainPassword', RepeatedType::class,
            [
                'type' => PasswordType::class,
                'invalid_message' => $this->translator->trans('admin.form.user.password.repeat.invalid'),
                'first_options'  => ['label' => $this->translator->trans('admin.form.user.password.new.label')],
                'second_options' => ['label' => $this->translator->trans('admin.form.user.password.repeat.label')],
                'attr' => [
                    'class' => 'password-field'
                ],
                'mapped' => false,
                'required' => true
            ])
            ->add('submit', SubmitType::class,
            [
                'label' => $this->translator->trans('admin.submit.button.label'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
