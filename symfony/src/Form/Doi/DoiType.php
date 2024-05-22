<?php

namespace App\Form\Doi;

use App\Entity\Doi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoiType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uri', UrlType::class,
            [
                'label' => 'URI',
                'attr' => [
                    'placeholder' => 'https://doi.org/'
                ]
            ])
            ->add('citation', TextareaType::class,
            [
                'label' => 'Citation',
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.form.doi.citation.placeholder')
                ]
            ])
            ->add('submit', SubmitType::class,
            [
                'label' => $this->translator->trans('admin.submit.button.label')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Doi::class,
        ]);
    }
}
