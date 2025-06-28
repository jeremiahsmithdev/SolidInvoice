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

namespace SolidInvoice\ClientBundle\Form\Type;

use SolidInvoice\ClientBundle\Entity\Address;
use SolidInvoice\ClientBundle\Entity\AdditionalContactDetail;
use SolidInvoice\ClientBundle\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

/**
 * @see \SolidInvoice\ClientBundle\Tests\Form\Type\ClientTypeTest
 */
class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('firstName', null, ['label' => 'client.form.first_name']);
        $builder->add('lastName', null, ['label' => 'client.form.last_name', 'required' => false]);
        $builder->add('phone', TelType::class, ['label' => 'Phone', 'required' => false]);
        $builder->add('email', EmailType::class, ['label' => 'client.form.email']);

        $builder->add(
            'additionalContactDetails',
            LiveCollectionType::class,
            [
                'entry_type' => ContactDetailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'label' => 'contact_details',
                'mapped' => false,
            ]
        );

        $builder->add(
            'addresses',
            LiveCollectionType::class,
            [
                'entry_type' => AddressType::class,
                'entry_options' => [
                    'data_class' => Address::class,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
            'validation_groups' => ['Default', 'form'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'client';
    }
}
