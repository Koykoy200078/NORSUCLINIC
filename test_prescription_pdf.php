<?php

require_once 'vendor/autoload.php';

// Test the prescription PDF functionality
try {
    echo "Testing prescription PDF functionality...\n";

    // Simulate a request to the prescription PDF
    $url = 'http://127.0.0.1:8000/prescription-pdf/5';

    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'method' => 'GET',
            'header' => "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n"
        ]
    ]);

    $result = file_get_contents($url, false, $context);

    if ($result !== false) {
        echo "✅ SUCCESS: Prescription PDF generated successfully!\n";
        echo "Response length: " . strlen($result) . " bytes\n";

        // Check if it's a PDF (starts with %PDF)
        if (substr($result, 0, 4) === '%PDF') {
            echo "✅ SUCCESS: Valid PDF format detected\n";
        } else {
            echo "⚠️  WARNING: Response doesn't appear to be a PDF\n";
            echo "First 100 characters: " . substr($result, 0, 100) . "\n";
        }
    } else {
        echo "❌ ERROR: Failed to get prescription PDF\n";
        echo "HTTP response headers: " . print_r($http_response_header ?? [], true) . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
