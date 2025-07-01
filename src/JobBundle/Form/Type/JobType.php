<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\JobBundle\Form\Type;

use SolidInvoice\ClientBundle\Form\ClientAutocompleteType;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\QuoteBundle\Form\QuoteAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'client',
                ClientAutocompleteType::class,
                [
                    'attr' => [
                        'class' => 'client-select',
                    ],
                    'placeholder' => 'job.client.choose',
                    'required' => true,
                ]
            )
            ->add(
                'quote',
                QuoteAutocompleteType::class,
                [
                    'attr' => [
                        'class' => 'quote-select',
                    ],
                    'placeholder' => 'job.quote.choose',
                    'required' => false,
                ]
            )
            ->add(
                'status',
                ChoiceType::class,
                [
                    'choices' => [
                        'Pending' => Job::STATUS_PENDING,
                        'In Progress' => Job::STATUS_IN_PROGRESS,
                        'Done' => Job::STATUS_DONE,
                        'Cancelled' => Job::STATUS_CANCELLED,
                        'Archived' => Job::STATUS_ARCHIVED,
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'attr' => [
                        'rows' => 5,
                    ],
                ]
            )
            ->add(
                'scheduledDate',
                DateType::class,
                [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => true,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Job::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'job';
    }
}
