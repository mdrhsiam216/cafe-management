<?php
/**
 * Send a JSON success response. Clears any prior output to avoid JSON parse errors
 * if warnings or notices were emitted earlier.
 */
function sendSuccess($data = null, $message = '') {
    // Clear any accidental output that could break JSON
    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

/**
 * Send a JSON error response. Also clears output buffers first.
 */
function sendError($message, $code = 400) {
    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Keep logging disabled for minimal setup; use error_log() or a proper logger if needed.
?>