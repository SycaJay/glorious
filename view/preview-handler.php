<?php
// preview-handler.php
function get_page_preview($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $html = curl_exec($ch);
    
    if(curl_errno($ch)) {
        return [
            'status' => 'error',
            'message' => curl_error($ch)
        ];
    }
    
    curl_close($ch);
    
    // Load HTML into DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    // Create an XPath object
    $xpath = new DOMXPath($dom);
    
    // Try to find the main content area using common selectors
    $contentSelectors = [
        "//main",
        "//div[contains(@class, 'main-content')]",
        "//div[contains(@class, 'content')]",
        "//article",
        "//div[contains(@class, 'container')]"
    ];
    
    $content = '';
    foreach ($contentSelectors as $selector) {
        $elements = $xpath->query($selector);
        if ($elements->length > 0) {
            $content = $dom->saveHTML($elements->item(0));
            break;
        }
    }
    
    // If no main content found, take the body content but remove header and footer
    if (empty($content)) {
        $body = $xpath->query("//body")->item(0);
        if ($body) {
            // Remove header/nav
            $headers = $xpath->query("//header | //nav");
            foreach ($headers as $header) {
                $header->parentNode->removeChild($header);
            }
            
            // Remove footer
            $footers = $xpath->query("//footer");
            foreach ($footers as $footer) {
                $footer->parentNode->removeChild($footer);
            }
            
            $content = $dom->saveHTML($body);
        }
    }
    
    return [
        'status' => 'success',
        'content' => $content,
        'timestamp' => time()
    ];
}

// API endpoint to get previews
if(isset($_GET['page'])) {
    header('Content-Type: application/json');
    
    $preview_urls = [
        'movie-channel' => 'https://gloriousvisionstvplus.com/view/movie.php',
        'herrnhut' => 'https://gloriousvisionstvplus.com/view/herrnhut.php',
        'studio' => 'https://gloriousvisionstvplus.com/view/studio.php',
        'teens' => 'https://gloriousvisionstvplus.com/view/teens.php',
        'tota' => 'https://totaonline.com',
        'publishing' => 'https://njpublishingministry.com'
    ];
    
    $page = $_GET['page'];
    
    if(isset($preview_urls[$page])) {
        $preview_data = get_page_preview($preview_urls[$page]);
        echo json_encode($preview_data);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid page requested'
        ]);
    }
    exit;
}
?>