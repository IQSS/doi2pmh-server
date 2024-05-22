<?php

namespace App\Form\Configuration;

use App\Entity\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfigurationType extends AbstractType
{

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('repositoryName', TextType::class,
            [
                'label' => $this->translator->trans('admin.form.config.repoName.label'),
            ])
            ->add('adminEmail', EmailType::class,
            [
                'label' => $this->translator->trans('admin.form.config.adminEmail.label'),
                'required' => true
            ])
            ->add('earliestDatestamp', DateType::class,
            [
                'label' => $this->translator->trans('admin.form.config.earliestDate.label'),
            ])
            ->add('excludedTypes', CollectionType::class,
            [
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'label' => $this->translator->trans('admin.form.config.excludedTypes.label'),
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_options' => [
                    'required' => false
                ]
            ])
            ->add('submit', SubmitType::class,
            [
                'attr' => ['class' => "btn btn-primary"],
                'label' => $this->translator->trans('admin.submit.button.label')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
        ]);
    }
}
