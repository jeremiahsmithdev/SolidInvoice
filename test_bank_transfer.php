#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Boot the Symfony kernel
$kernel = new \SolidInvoice\Kernel($_ENV['SOLIDINVOICE_ENV'] ?? 'dev', (bool) ($_ENV['SOLIDINVOICE_DEBUG'] ?? true));
$kernel->boot();

$container = $kernel->getContainer();

// Get the entity manager and repository
$entityManager = $container->get('doctrine.orm.entity_manager');
$paymentMethodRepository = $entityManager->getRepository(\SolidInvoice\PaymentBundle\Entity\PaymentMethod::class);

echo "Testing Bank Transfer Configuration...\n\n";

// Find the bank transfer payment method
$bankTransferMethod = $paymentMethodRepository->findOneBy(['gatewayName' => 'bank_transfer']);

if ($bankTransferMethod) {
    echo "✓ Bank Transfer payment method found\n";
    echo "  ID: " . $bankTransferMethod->getId() . "\n";
    echo "  Name: " . $bankTransferMethod->getName() . "\n";
    echo "  Gateway Name: " . $bankTransferMethod->getGatewayName() . "\n";
    echo "  Factory Name: " . $bankTransferMethod->getFactoryName() . "\n";
    echo "  Is Enabled: " . ($bankTransferMethod->isEnabled() ? 'Yes' : 'No') . "\n";
    echo "  Is Internal: " . ($bankTransferMethod->isInternal() ? 'Yes' : 'No') . "\n";
    echo "  Configuration:\n";
    
    $config = $bankTransferMethod->getConfig();
    if ($config && !empty($config)) {
        foreach ($config as $key => $value) {
            if ($key !== 'factory') {
                echo "    $key: $value\n";
            }
        }
    } else {
        echo "    No configuration found\n";
    }
} else {
    echo "✗ Bank Transfer payment method not found\n";
    
    // List all payment methods
    $allMethods = $paymentMethodRepository->findAll();
    echo "\nAvailable payment methods:\n";
    foreach ($allMethods as $method) {
        echo "  - " . $method->getGatewayName() . " (" . $method->getName() . ")\n";
    }
}

echo "\nTest completed.\n";