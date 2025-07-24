<?php
/**
 * Creates Olobase module dependencies config file.
 */
$targetFile = __DIR__ . '/../config/autoload/module.dependencies.global.php';

define('MODULE_DEPENDENCIES_URL', 'https://olobase.dev/api/command/modules/findAllDependencies');

echo "Fetching module dependency list from Oloma Server...\n";

$ch = curl_init(MODULE_DEPENDENCIES_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

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
