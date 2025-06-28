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

namespace SolidInvoice\MapBundle\Command;

use SolidInvoice\ClientBundle\Entity\Address;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Model\Status;
use SolidInvoice\CoreBundle\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'map:create-test-client',
    description: 'Create a test client with address for map visualization testing'
)]
class CreateTestClientCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get the first available company
        $company = $this->entityManager->getRepository(Company::class)->findOneBy([]);
        
        if (!$company) {
            $io->error('No company found. Please create a company first.');
            return Command::FAILURE;
        }

        // Create test clients with addresses
        $testClients = [
            [
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'john.smith@example.com',
                'address' => [
                    'street1' => '123 Main Street',
                    'street2' => 'Suite 100',
                    'city' => 'Sydney',
                    'state' => 'NSW',
                    'zip' => '2000',
                    'country' => 'AU',
                ]
            ],
            [
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'email' => 'jane.doe@company.com',
                'address' => [
                    'street1' => '456 Business Avenue',
                    'street2' => '',
                    'city' => 'Melbourne',
                    'state' => 'VIC',
                    'zip' => '3000',
                    'country' => 'AU',
                ]
            ],
            [
                'firstName' => 'Bob',
                'lastName' => 'Wilson',
                'email' => 'bob.wilson@tech.com',
                'address' => [
                    'street1' => '789 Innovation Drive',
                    'street2' => 'Building A',
                    'city' => 'Brisbane',
                    'state' => 'QLD',
                    'zip' => '4000',
                    'country' => 'AU',
                ]
            ],
            [
                'firstName' => 'Sarah',
                'lastName' => 'Johnson',
                'email' => 'sarah.johnson@design.com',
                'address' => [
                    'street1' => '321 Creative Lane',
                    'street2' => 'Studio 5',
                    'city' => 'Perth',
                    'state' => 'WA',
                    'zip' => '6000',
                    'country' => 'AU',
                ]
            ]
        ];

        $createdClients = [];

        foreach ($testClients as $clientData) {
            // Check if client already exists
            $existingClient = $this->entityManager->getRepository(Client::class)
                ->findOneBy(['email' => $clientData['email'], 'company' => $company]);

            if ($existingClient) {
                $io->note(sprintf('Client %s already exists, skipping.', $clientData['email']));
                continue;
            }

            // Create client
            $client = new Client();
            $client->setFirstName($clientData['firstName']);
            $client->setLastName($clientData['lastName']);
            $client->setEmail($clientData['email']);
            $client->setStatus(Status::STATUS_ACTIVE);
            $client->setCompany($company);
            
            // Set company on the credit as well
            if ($client->getCredit()) {
                $client->getCredit()->setCompany($company);
            }

            $this->entityManager->persist($client);

            // Create address
            $address = new Address();
            $address->setStreet1($clientData['address']['street1']);
            $address->setStreet2($clientData['address']['street2']);
            $address->setCity($clientData['address']['city']);
            $address->setState($clientData['address']['state']);
            $address->setZip($clientData['address']['zip']);
            $address->setCountry($clientData['address']['country']);
            $address->setClient($client);
            $address->setCompany($company);

            $this->entityManager->persist($address);

            $createdClients[] = $clientData['firstName'] . ' ' . $clientData['lastName'];
        }

        if (empty($createdClients)) {
            $io->info('All test clients already exist.');
        } else {
            $this->entityManager->flush();
            $io->success(sprintf(
                'Created %d test clients with addresses: %s',
                count($createdClients),
                implode(', ', $createdClients)
            ));
        }

        $io->info('You can now view the clients on the map at /map');

        return Command::SUCCESS;
    }
}