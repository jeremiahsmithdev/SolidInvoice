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

namespace SolidInvoice\ClientBundle\Twig\Components;

use Doctrine\ORM\EntityManagerInterface;
use SolidInvoice\ClientBundle\Entity\Address;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Entity\Contact;
use SolidInvoice\ClientBundle\Entity\ContactType;
use SolidInvoice\ClientBundle\Entity\AdditionalContactDetail;
use SolidInvoice\ClientBundle\Form\Type\ClientType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;
use Symfony\UX\LiveComponent\Attribute\PostMount;

#[AsLiveComponent]
class CompactClientForm extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    #[LiveProp(fieldName: 'formData')]
    public ?Client $client = null;

    #[LiveProp]
    public ?string $createdClientId = null;

    #[LiveProp]
    public bool $isVisible = false;

    #[PostMount]
    public function postMount(): void
    {
        if ($this->client === null) {
            $this->client = (new Client())->addAddress(new Address());
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            ClientType::class,
            $this->client ?? (new Client())
                ->addAddress(new Address()),
            ['validation_groups' => ['Default']]
        );
    }

    public function getForm(): FormInterface
    {
        return $this->instantiateForm();
    }

    public function getFormView()
    {
        return $this->getForm()->createView();
    }


    #[LiveAction]
    public function save(EntityManagerInterface $manager): Response
    {
        // Use the client property that gets populated via data-model bindings
        $client = $this->client ?? new Client();
        
        // Ensure we have an address if one was provided
        if ($client->getAddresses()->isEmpty()) {
            $client->addAddress(new Address());
        }

        // Automatically create a Contact from the client data if no contacts exist
        // This must be done BEFORE validation because the 'form' validation group requires at least one contact
        if ($client->getContacts()->isEmpty()) {
            $contact = new Contact();
            $contact->setFirstName($client->getFirstName());
            $contact->setLastName($client->getLastName());
            $contact->setEmail($client->getEmail());
            $contact->setClient($client);
            // Note: setCompany will be handled by the CompanyListener during persist
            
            $client->addContact($contact);
        }

        // Validate the client manually instead of using form validation
        $violations = $this->container->get('validator')->validate($client, null, ['Default']);
        
        if (count($violations) > 0) {
            // Handle validation errors - for now just throw an error
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            return new Response('Validation failed: ' . implode(', ', $errorMessages), 422);
        }

        foreach ($client->getAddresses() as $address) {
            if ($address->isEmpty()) {
                $client->removeAddress($address);
            }
        }

        // Persist the contact if it was created
        $contact = $client->getContacts()->first();
        if ($contact) {
            $manager->persist($contact);
        }

        $manager->persist($client);
        $manager->flush();

        // After flush, the company will be set by CompanyListener, so we can now add additional details
        
        // Add phone number as additional contact detail if provided
        if ($client->getPhone() && $contact) {
            $phoneType = $manager->getRepository(ContactType::class)
                ->findOneBy(['name' => 'phone', 'company' => $client->getCompany()]);
            
            if ($phoneType) {
                $phoneDetail = new AdditionalContactDetail();
                $phoneDetail->setValue($client->getPhone());
                $phoneDetail->setType($phoneType);
                $phoneDetail->setContact($contact);
                $phoneDetail->setCompany($client->getCompany());
                
                $contact->addAdditionalContactDetail($phoneDetail);
                $manager->persist($phoneDetail);
            }
        }

        // Final flush for additional contact details
        if ($contact && $client->getPhone()) {
            $manager->flush();
        }

        // Set the created client ID
        $this->createdClientId = $client->getId()->toRfc4122();

        $this->addFlash('success', 'Client created successfully');

        // Return a response that triggers the parent form to update
        // Hide the form after successful creation
        $this->isVisible = false;

        return new Response('', 200, [
            'HX-Trigger' => json_encode([
                'clientCreated' => [
                    'id' => $this->createdClientId,
                    'name' => $client->getName(),
                    'firstName' => $client->getFirstName(),
                    'lastName' => $client->getLastName(),
                    'email' => $client->getEmail(),
                ]
            ])
        ]);
    }

    #[LiveAction]
    public function toggle(): void
    {
        $this->isVisible = !$this->isVisible;
    }
}