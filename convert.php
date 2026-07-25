<?php

$srcDir = __DIR__ . '/website';
$destDir = __DIR__ . '/resources/views/front';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}
if (!is_dir($destDir . '/layouts')) {
    mkdir($destDir . '/layouts', 0755, true);
}

// Read index.html to extract layout
$indexContent = file_get_contents($srcDir . '/index.html');

// Replace asset URLs
$indexContent = preg_replace('/href="css\//', 'href="{{ asset(\'front_assets/css/', $indexContent);
$indexContent = preg_replace('/href="images\//', 'href="{{ asset(\'front_assets/images/', $indexContent);
$indexContent = preg_replace('/src="js\//', 'src="{{ asset(\'front_assets/js/', $indexContent);
$indexContent = preg_replace('/src="images\//', 'src="{{ asset(\'front_assets/images/', $indexContent);
$indexContent = preg_replace('/\.css"/', '.css\') }}"', $indexContent);
$indexContent = preg_replace('/\.js"/', '.js\') }}"', $indexContent);
$indexContent = preg_replace('/\.jpg"/', '.jpg\') }}"', $indexContent);
$indexContent = preg_replace('/\.png"/', '.png\') }}"', $indexContent);
$indexContent = preg_replace('/\.svg"/', '.svg\') }}"', $indexContent);

$indexContent = str_replace('href="index.html"', 'href="{{ route(\'/\') }}"', $indexContent);
$indexContent = str_replace('href="about.html"', 'href="{{ url(\'/about\') }}"', $indexContent);
$indexContent = str_replace('href="services.html"', 'href="{{ url(\'/services\') }}"', $indexContent);
$indexContent = str_replace('href="contact.html"', 'href="{{ url(\'/contact\') }}"', $indexContent);

$indexContent = str_replace('>get started<', '>Login<', $indexContent);
$indexContent = preg_replace('/href="[^"]*"\s+class="btn-default btn-highlighted"[^>]*>Login</', 'href="{{ route(\'admin.login\') }}" class="btn-default btn-highlighted">Login<', $indexContent);


$headerEndPos = strpos($indexContent, '<!-- Header End -->') + strlen('<!-- Header End -->');
$footerStartPos = strpos($indexContent, '<!-- Footer Main Start -->');

$header = substr($indexContent, 0, $headerEndPos);
$footer = substr($indexContent, $footerStartPos);

$trackingLink = '<li class="nav-item"><a class="nav-link" href="{{ url(\'/tracking\') }}">Tracking</a></li>';
$header = str_replace('<li class="nav-item"><a class="nav-link" href="{{ url(\'/contact\') }}">Contact Us</a></li>', $trackingLink . "\n" . '<li class="nav-item"><a class="nav-link" href="{{ url(\'/contact\') }}">Contact Us</a></li>', $header);

$layout = $header . "\n\n@yield('content')\n\n" . $footer;
file_put_contents($destDir . '/layouts/app.blade.php', $layout);

function processPage($src, $dest) {
    global $srcDir, $destDir;
    $content = file_get_contents($srcDir . '/' . $src);
    
    $content = preg_replace('/href="css\//', 'href="{{ asset(\'front_assets/css/', $content);
    $content = preg_replace('/href="images\//', 'href="{{ asset(\'front_assets/images/', $content);
    $content = preg_replace('/src="js\//', 'src="{{ asset(\'front_assets/js/', $content);
    $content = preg_replace('/src="images\//', 'src="{{ asset(\'front_assets/images/', $content);
    $content = preg_replace('/\.css"/', '.css\') }}"', $content);
    $content = preg_replace('/\.js"/', '.js\') }}"', $content);
    $content = preg_replace('/\.jpg"/', '.jpg\') }}"', $content);
    $content = preg_replace('/\.png"/', '.png\') }}"', $content);
    $content = preg_replace('/\.svg"/', '.svg\') }}"', $content);
    $content = str_replace('href="index.html"', 'href="{{ route(\'/\') }}"', $content);
    $content = str_replace('href="about.html"', 'href="{{ url(\'/about\') }}"', $content);
    $content = str_replace('href="services.html"', 'href="{{ url(\'/services\') }}"', $content);
    $content = str_replace('href="contact.html"', 'href="{{ url(\'/contact\') }}"', $content);

    $hEnd = strpos($content, '<!-- Header End -->');
    if ($hEnd !== false) {
        $hEnd += strlen('<!-- Header End -->');
    } else {
        $hEnd = 0;
    }
    
    $fStart = strpos($content, '<!-- Footer Main Start -->');
    if ($fStart === false) {
        $fStart = strlen($content);
    }

    $body = substr($content, $hEnd, $fStart - $hEnd);
    
    if ($src === 'contact.html') {
        $body = str_replace('<form id="contactForm" action="#" method="POST">', '<form id="contactForm" action="{{ url(\'/contact\') }}" method="POST">' . "\n" . '@csrf', $body);
        $body = str_replace('name="subject"', 'name="subject" id="subject"', $body); // minor fix if needed
        
        // Let's add session flash message display in the body
        $alert = "@if(session('success'))\n<div class=\"alert alert-success\">{{ session('success') }}</div>\n@endif\n";
        $body = str_replace('<div class="contact-us-form">', '<div class="contact-us-form">' . "\n" . $alert, $body);
    }
    
    $blade = "@extends('front.layouts.app')\n@section('content')\n" . trim($body) . "\n@endsection\n";
    file_put_contents($destDir . '/' . $dest, $blade);
}

processPage('index.html', 'home.blade.php');
processPage('about.html', 'about.blade.php');
processPage('services.html', 'services.blade.php');
processPage('contact.html', 'contact.blade.php');

echo "Conversion complete.\n";
