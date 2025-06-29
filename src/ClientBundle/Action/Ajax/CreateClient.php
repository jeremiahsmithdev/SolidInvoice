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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateClient
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $client = $this->createClientFromData($data);
            
            // Validate the client
            $violations = $this->validator->validate($client);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                return new JsonResponse(['error' => 'Validation failed', 'details' => $errors], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            return new JsonResponse([
                'id' => $client->getId()->toRfc4122(),
                'firstName' => $client->getFirstName(),
                'lastName' => $client->getLastName(),
                'email' => $client->getEmail(),
                'phone' => $client->getPhone(),
                'name' => $client->getName(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to create client: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createClientFromData(array $data): Client
    {
        $client = new Client();
        $client->setFirstName($data['firstName'] ?? '');
        $client->setLastName($data['lastName'] ?? '');
        $client->setEmail($data['email'] ?? '');
        
        if (!empty($data['phone'])) {
            $client->setPhone($data['phone']);
        }

        // Create address if provided
        if (!empty($data['address']) && array_filter($data['address'])) {
            $address = new Address();
            $addressData = $data['address'];
            
            if (!empty($addressData['street1'])) {
                $address->setStreet1($addressData['street1']);
            }
            if (!empty($addressData['city'])) {
                $address->setCity($addressData['city']);
            }
            if (!empty($addressData['state'])) {
                $address->setState($addressData['state']);
            }
            if (!empty($addressData['zip'])) {
                $address->setZip($addressData['zip']);
            }
            
            $address->setClient($client);
            $client->addAddress($address);
        }

        // Create a contact from client data (as we do in the regular client form)
        $contact = new Contact();
        $contact->setFirstName($client->getFirstName());
        $contact->setLastName($client->getLastName());
        $contact->setEmail($client->getEmail());
        $contact->setClient($client);
        
        $client->addContact($contact);

        return $client;
    }
}