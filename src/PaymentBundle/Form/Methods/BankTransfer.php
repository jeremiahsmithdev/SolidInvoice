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

namespace SolidInvoice\PaymentBundle\Form\Methods;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class BankTransfer extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'bank_name',
            TextType::class,
            [
                'label' => 'Bank Name',
                'constraints' => new NotBlank(),
                'help' => 'Name of the bank (e.g., Commonwealth Bank)',
            ]
        );

        $builder->add(
            'account_name',
            TextType::class,
            [
                'label' => 'Account Name',
                'constraints' => new NotBlank(),
                'help' => 'Name on the bank account',
            ]
        );

        $builder->add(
            'bsb',
            TextType::class,
            [
                'label' => 'BSB',
                'constraints' => new NotBlank(),
                'help' => 'Bank State Branch number (6 digits)',
                'attr' => [
                    'placeholder' => '123-456',
                    'maxlength' => 7,
                ],
            ]
        );

        $builder->add(
            'account_number',
            TextType::class,
            [
                'label' => 'Account Number',
                'constraints' => new NotBlank(),
                'help' => 'Bank account number',
                'attr' => [
                    'placeholder' => '12345678',
                ],
            ]
        );

        $builder->add(
            'reference_instructions',
            TextType::class,
            [
                'label' => 'Payment Reference Instructions',
                'required' => false,
                'help' => 'Instructions for customers on payment reference (e.g., "Please use invoice number as reference")',
                'attr' => [
                    'placeholder' => 'Please use invoice number as reference',
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'bank_transfer';
    }
}