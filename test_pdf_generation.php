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

// Get the PDF generator
$pdfGenerator = $container->get(\SolidInvoice\CoreBundle\Pdf\Generator::class);

echo "Testing PDF Generation...\n\n";

// Check if PDF generation is available
if (!$pdfGenerator->canPrintPdf()) {
    echo "✗ PDF generation is not available\n";
    echo "Required extensions:\n";
    echo "  - mbstring: " . (extension_loaded('mbstring') ? 'Yes' : 'No') . "\n";
    echo "  - gd: " . (extension_loaded('gd') ? 'Yes' : 'No') . "\n";
    exit(1);
}

echo "✓ PDF generation is available\n";

// Test simple HTML to PDF
$simpleHtml = '<html><body><h1>Test PDF</h1><p>This is a test PDF document.</p></body></html>';

try {
    $pdfContent = $pdfGenerator->generate($simpleHtml);
    echo "✓ Simple PDF generated successfully\n";
    echo "  PDF size: " . strlen($pdfContent) . " bytes\n";
    echo "  PDF starts with: " . substr($pdfContent, 0, 10) . "\n";
    
    // Save test PDF
    file_put_contents('test_simple.pdf', $pdfContent);
    echo "✓ Test PDF saved as test_simple.pdf\n";
    
} catch (Exception $e) {
    echo "✗ PDF generation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Now test with an invoice template
$entityManager = $container->get('doctrine.orm.entity_manager');
$invoiceRepository = $entityManager->getRepository(\SolidInvoice\InvoiceBundle\Entity\Invoice::class);

$invoice = $invoiceRepository->findOneBy([]);

if (!$invoice) {
    echo "✗ No invoices found in database\n";
    exit(1);
}

echo "✓ Found invoice: " . $invoice->getInvoiceId() . "\n";

// Get the Twig environment
$twig = $container->get('twig');

try {
    $invoiceHtml = $twig->render('@SolidInvoiceInvoice/Pdf/invoice.html.twig', ['invoice' => $invoice]);
    echo "✓ Invoice template rendered successfully\n";
    echo "  HTML size: " . strlen($invoiceHtml) . " bytes\n";
    
    // Save the HTML for debugging
    file_put_contents('test_invoice.html', $invoiceHtml);
    echo "✓ Invoice HTML saved as test_invoice.html\n";
    
    // Generate PDF from invoice template
    $invoicePdfContent = $pdfGenerator->generate($invoiceHtml);
    echo "✓ Invoice PDF generated successfully\n";
    echo "  PDF size: " . strlen($invoicePdfContent) . " bytes\n";
    
    // Save invoice PDF
    file_put_contents('test_invoice.pdf', $invoicePdfContent);
    echo "✓ Invoice PDF saved as test_invoice.pdf\n";
    
} catch (Exception $e) {
    echo "✗ Invoice PDF generation failed: " . $e->getMessage() . "\n";
    echo "  Error type: " . get_class($e) . "\n";
    echo "  Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";