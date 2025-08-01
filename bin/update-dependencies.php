<?php

declare(strict_types=1);

use Olobase\ModuleManager\Server;

/**
 * Creates Olobase module dependencies config file.
 */
$targetFile = __DIR__ . '/../config/autoload/module.dependencies.global.php';

echo "Fetching module dependency list from Oloma Server...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'header' => "Accept: application/json\r\n"
    ]
]);

$url = Server::getModuleDependenciesUrl();
$response = file_get_contents($url, false, $context);

$httpCode = null;
if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (preg_match('#^HTTP/\d+\.\d+\s+(\d+)#', $header, $matches)) {
            $httpCode = (int)$matches[1];
            break;
        }
    }
}

if ($httpCode !== 200 || !$response) {
    echo "\033[31mFetching dependency list failed from Oloma Server.\033[0m\n";
    exit(1);
}

$modules = json_decode($response, true);
if (!is_array($modules)) {
    echo "\033[31mInvalid response from Oloma Server.\033[0m\n";
    exit(1);
}

$config = [
    'module_dependencies' => $modules
];

// Dosyayı oluştur
$content = "<?php\n\nreturn " . var_export($config, true) . ";\n";

if (file_exists($targetFile)) {
    unlink($targetFile);
}

file_put_contents($targetFile, $content);
echo "\033[32mSaved module dependencies to: $targetFile\033[0m\n";
