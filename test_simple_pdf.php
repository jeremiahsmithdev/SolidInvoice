#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

echo "Testing mPDF directly...\n\n";

// Check if required extensions are loaded
if (!extension_loaded('mbstring')) {
    echo "✗ mbstring extension is not loaded\n";
    exit(1);
}

if (!extension_loaded('gd')) {
    echo "✗ gd extension is not loaded\n";
    exit(1);
}

echo "✓ Required extensions are loaded\n";

try {
    // Create a simple mPDF instance
    $mpdf = new Mpdf([
        'tempDir' => sys_get_temp_dir(),
        'margin_left' => 20,
        'margin_right' => 15,
        'margin_top' => 48,
        'margin_bottom' => 25,
        'margin_header' => 10,
        'margin_footer' => 10,
    ]);

    echo "✓ mPDF instance created successfully\n";

    // Test simple HTML
    $simpleHtml = '<html><body><h1>Test PDF</h1><p>This is a simple test.</p></body></html>';
    
    $mpdf->WriteHTML($simpleHtml);
    $pdfContent = $mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
    
    echo "✓ Simple PDF generated successfully\n";
    echo "  PDF size: " . strlen($pdfContent) . " bytes\n";
    
    file_put_contents('test_mpdf_simple.pdf', $pdfContent);
    echo "✓ Simple PDF saved as test_mpdf_simple.pdf\n";

    // Test with CSS
    $htmlWithCss = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            h1 { color: blue; }
            .test { background-color: yellow; padding: 10px; }
        </style>
    </head>
    <body>
        <h1>Test PDF with CSS</h1>
        <div class="test">This is a test with CSS styling.</div>
        <p>This should have styling applied.</p>
    </body>
    </html>';
    
    $mpdf2 = new Mpdf([
        'tempDir' => sys_get_temp_dir(),
        'margin_left' => 20,
        'margin_right' => 15,
        'margin_top' => 48,
        'margin_bottom' => 25,
        'margin_header' => 10,
        'margin_footer' => 10,
    ]);
    
    $mpdf2->WriteHTML($htmlWithCss);
    $pdfContent2 = $mpdf2->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
    
    echo "✓ CSS PDF generated successfully\n";
    echo "  PDF size: " . strlen($pdfContent2) . " bytes\n";
    
    file_put_contents('test_mpdf_css.pdf', $pdfContent2);
    echo "✓ CSS PDF saved as test_mpdf_css.pdf\n";

} catch (Exception $e) {
    echo "✗ mPDF test failed: " . $e->getMessage() . "\n";
    echo "  Error type: " . get_class($e) . "\n";
    exit(1);
}

echo "\nmPDF test completed successfully.\n";