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

namespace SolidInvoice\ClientBundle\Action\Ajax;

use Doctrine\ORM\EntityManagerInterface;
use SolidInvoice\ClientBundle\Entity\Address;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Entity\Contact;
use SolidInvoice\ClientBundle\Entity\ContactType;
use SolidInvoice\ClientBundle\Entity\AdditionalContactDetail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateClientCompact
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            // Get form data
            $firstName = $request->request->get('firstName', '');
            $lastName = $request->request->get('lastName', '');
            $email = $request->request->get('email', '');
            $phone = $request->request->get('phone', '');
            
            // Create client
            $client = new Client();
            $client->setFirstName($firstName);
            $client->setLastName($lastName);
            $client->setEmail($email);
            if ($phone) {
                $client->setPhone($phone);
            }
            
            // Create address if provided
            $street1 = $request->request->get('street1', '');
            $city = $request->request->get('city', '');
            $state = $request->request->get('state', '');
            $zip = $request->request->get('zip', '');
            
            if ($street1 || $city || $state || $zip) {
                $address = new Address();
                $address->setStreet1($street1);
                $address->setCity($city);
                $address->setState($state);
                $address->setZip($zip);
                $address->setClient($client);
                $client->addAddress($address);
            }
            
            // Create contact from client data
            $contact = new Contact();
            $contact->setFirstName($client->getFirstName());
            $contact->setLastName($client->getLastName());
            $contact->setEmail($client->getEmail());
            $contact->setClient($client);
            $client->addContact($contact);
            
            // Validate the client
            $violations = $this->validator->validate($client);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                return new JsonResponse(['error' => 'Validation failed', 'details' => $errors], Response::HTTP_BAD_REQUEST);
            }

            // Persist
            $this->entityManager->persist($contact);
            $this->entityManager->persist($client);
            $this->entityManager->flush();
            
            // Add phone as contact detail if provided
            if ($client->getPhone()) {
                $phoneType = $this->entityManager->getRepository(ContactType::class)
                    ->findOneBy(['name' => 'phone', 'company' => $client->getCompany()]);
                
                if ($phoneType) {
                    $phoneDetail = new AdditionalContactDetail();
                    $phoneDetail->setValue($client->getPhone());
                    $phoneDetail->setType($phoneType);
                    $phoneDetail->setContact($contact);
                    $phoneDetail->setCompany($client->getCompany());
                    
                    $contact->addAdditionalContactDetail($phoneDetail);
                    $this->entityManager->persist($phoneDetail);
                    $this->entityManager->flush();
                }
            }

            return new JsonResponse([
                'success' => true,
                'client' => [
                    'id' => $client->getId()->toRfc4122(),
                    'firstName' => $client->getFirstName(),
                    'lastName' => $client->getLastName(),
                    'email' => $client->getEmail(),
                    'phone' => $client->getPhone(),
                    'name' => $client->getName(),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to create client: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}