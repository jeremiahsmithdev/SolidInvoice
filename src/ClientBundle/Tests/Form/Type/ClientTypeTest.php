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

namespace SolidInvoice\ClientBundle\Tests\Form\Type;

use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Form\Type\ClientType;
use SolidInvoice\ClientBundle\Form\Type\ContactDetailType;
use SolidInvoice\CoreBundle\Tests\FormTestCase;
use Symfony\Component\Form\PreloadedExtension;

class ClientTypeTest extends FormTestCase
{
    public function testSubmit(): void
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $email = $this->faker->email;
        $url = $this->faker->url;

        $formData = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'addresses' => [],
        ];

        $object = new Client();
        $object->setFirstName($firstName);
        $object->setLastName($lastName);
        $object->setEmail($email);

        $this->assertFormData(ClientType::class, $formData, $object);
    }

    /**
     * @return PreloadedExtension[]
     */
    protected function getExtensions(): array
    {
        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([new ContactDetailType()], []),
        ];
    }
}
