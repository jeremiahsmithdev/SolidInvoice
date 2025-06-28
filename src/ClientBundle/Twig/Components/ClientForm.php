<?php

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
use SolidInvoice\ClientBundle\Entity\AdditionalContactDetail;
use SolidInvoice\ClientBundle\Entity\Address;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Entity\Contact;
use SolidInvoice\ClientBundle\Entity\ContactType;
use SolidInvoice\ClientBundle\Form\Type\ClientType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent]
class ClientForm extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    #[LiveProp(fieldName: 'formData')]
    public ?Client $client = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            ClientType::class,
            $this->client ?? (new Client())
                ->addAddress(new Address()),
            ['validation_groups' => ['Default']]
        );
    }

    #[LiveAction]
    public function save(EntityManagerInterface $manager): RedirectResponse
    {
        $this->submitForm();

        /** @var Client $client */
        $client = $this->getForm()->getData();

        // Automatically create a Contact from the client data if no contacts exist
        if ($client->getContacts()->isEmpty()) {
            $contact = new Contact();
            $contact->setFirstName($client->getFirstName());
            $contact->setLastName($client->getLastName());
            $contact->setEmail($client->getEmail());
            $contact->setClient($client);
            // Note: setCompany will be handled by the CompanyListener during persist
            
            $client->addContact($contact);
            $manager->persist($contact);
        }

        // Get additional contact details from the unmapped form field
        $additionalContactDetails = $this->getForm()->get('additionalContactDetails')->getData();

        foreach ($client->getAddresses() as $address) {
            if ($address->isEmpty()) {
                $client->removeAddress($address);
            }
        }

        $manager->persist($client);
        $manager->flush();

        // After flush, the company will be set by CompanyListener, so we can now add additional details
        $contact = $client->getContacts()->first();
        
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

        // Add additional contact details from the form
        if ($additionalContactDetails && $contact) {
            foreach ($additionalContactDetails as $detail) {
                if ($detail instanceof AdditionalContactDetail && $detail->getType() && $detail->getValue()) {
                    $detail->setContact($contact);
                    $detail->setCompany($client->getCompany());
                    $contact->addAdditionalContactDetail($detail);
                    $manager->persist($detail);
                }
            }
        }

        // Final flush for additional contact details
        if ($contact && ($client->getPhone() || $additionalContactDetails)) {
            $manager->flush();
        }

        $this->addFlash('success', 'client.create.success');

        return $this->redirectToRoute('_clients_view', [
            'id' => $client->getId(),
        ]);
    }
}
