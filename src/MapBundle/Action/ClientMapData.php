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

namespace SolidInvoice\MapBundle\Action;

use SolidInvoice\ClientBundle\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ClientMapData
{
    public function __construct(
        private readonly ClientRepository $clientRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $clients = $this->clientRepository->findAllWithAddresses();
        
        $mapData = [];
        
        foreach ($clients as $client) {
            foreach ($client->getAddresses() as $address) {
                if (!$address->isEmpty()) {
                    $mapData[] = [
                        'id' => $client->getId()->toString(),
                        'name' => trim($client->getFirstName() . ' ' . ($client->getLastName() ?? '')),
                        'email' => $client->getEmail(),
                        'address' => [
                            'street1' => $address->getStreet1(),
                            'street2' => $address->getStreet2(),
                            'city' => $address->getCity(),
                            'state' => $address->getState(),
                            'zip' => $address->getZip(),
                            'country' => $address->getCountry(),
                            'countryName' => $address->getCountryName(),
                            'formatted' => (string) $address,
                        ],
                    ];
                }
            }
        }
        
        return new JsonResponse($mapData);
    }
}