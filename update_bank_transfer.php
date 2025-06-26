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

echo "Updating Bank Transfer Configuration...\n\n";

// Find the bank transfer payment method
$bankTransferMethod = $paymentMethodRepository->findOneBy(['gatewayName' => 'bank_transfer']);

if ($bankTransferMethod) {
    echo "✓ Bank Transfer payment method found\n";
    echo "  Current Factory Name: " . $bankTransferMethod->getFactoryName() . "\n";
    
    // Update the factory name to 'offline'
    $bankTransferMethod->setFactoryName('offline');
    $bankTransferMethod->setInternal(true);
    
    $entityManager->persist($bankTransferMethod);
    $entityManager->flush();
    
    echo "✓ Updated factory name to: " . $bankTransferMethod->getFactoryName() . "\n";
    echo "✓ Updated internal flag to: " . ($bankTransferMethod->isInternal() ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ Bank Transfer payment method not found\n";
}

echo "\nUpdate completed.\n";