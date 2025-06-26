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

echo "Testing Invoice Template Rendering...\n\n";

// Get services
$entityManager = $container->get('doctrine.orm.entity_manager');
$twig = $container->get('twig');

// Find an invoice
$invoiceRepository = $entityManager->getRepository(\SolidInvoice\InvoiceBundle\Entity\Invoice::class);
$invoice = $invoiceRepository->findOneBy([]);

if (!$invoice) {
    echo "✗ No invoices found in database\n";
    exit(1);
}

echo "✓ Found invoice: " . $invoice->getInvoiceId() . "\n";

try {
    // Test rendering the regular invoice template first
    $regularHtml = $twig->render('@SolidInvoiceInvoice/Default/view.html.twig', [
        'invoice' => $invoice,
        'payments' => []
    ]);
    
    echo "✓ Regular invoice template rendered successfully\n";
    echo "  HTML size: " . strlen($regularHtml) . " bytes\n";
    
    // Save regular HTML
    file_put_contents('test_invoice_regular.html', $regularHtml);
    echo "✓ Regular HTML saved as test_invoice_regular.html\n";

} catch (Exception $e) {
    echo "✗ Regular template rendering failed: " . $e->getMessage() . "\n";
}

try {
    // Test rendering the PDF template
    $pdfHtml = $twig->render('@SolidInvoiceInvoice/Pdf/invoice.html.twig', ['invoice' => $invoice]);
    
    echo "✓ PDF invoice template rendered successfully\n";
    echo "  HTML size: " . strlen($pdfHtml) . " bytes\n";
    
    // Save PDF HTML
    file_put_contents('test_invoice_pdf.html', $pdfHtml);
    echo "✓ PDF HTML saved as test_invoice_pdf.html\n";
    
    // Check if the HTML contains any obvious issues
    if (strpos($pdfHtml, '<body>') === false) {
        echo "⚠ Warning: No <body> tag found in PDF HTML\n";
    }
    
    if (strpos($pdfHtml, 'Invoice #') === false) {
        echo "⚠ Warning: Invoice content not found in PDF HTML\n";
    }
    
    if (strpos($pdfHtml, 'file(asset(') !== false) {
        echo "⚠ Warning: Unprocessed file() function found in PDF HTML\n";
    }

} catch (Exception $e) {
    echo "✗ PDF template rendering failed: " . $e->getMessage() . "\n";
    echo "  Error type: " . get_class($e) . "\n";
    echo "  Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTemplate test completed.\n";