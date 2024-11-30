<?php


require 'vendor/autoload.php';

use Gettext\Loader\PoLoader;
use Gettext\Translations;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use function Termwind\render;

$dotenv = new Dotenv();
$dotenv->overload(__DIR__.'/.env');

// 確認命令列參數
if ($argc < 2 || empty($argv[1])) {
    // echo "Usage: php build.php <locale>\n";

    render(<<<'HTML'
        <div class="py-1 px-2">
            <div class="mb-1">
                <span class="text-stone-500 pr-2">Cornch's Laravel Doc L10N Build Tool</span>
            </div>
            
            <div>
                <span class="text-yellow-800 mr-2">USAGE:</span>
                <span>php build.php [local]</span>
            </div>
        </div>
        HTML);
    exit(1);
}

$locale = $argv[1];
$crowdinToken = $_ENV['CROWDIN_TOKEN'];

if ($crowdinToken === false) {
    render(<<<'HTML'
        <div class="py-1">
            <span class="text-red-500 px-1 mr-1">ERROR</span>
            <span><u>CROWDIN_TOKEN</u> environment variable is not set</span>
        </div>
        HTML);
    exit(1);
}

$fileTranslators = [];

function listFileTranslators($projectId, $fileId, $locale, $crowdinToken)
{
    global $fileTranslators;

    if (isset($fileTranslators[$fileId])) {
        return $fileTranslators[$fileId];
    }

    $translators = [];
    $hasMore = true;
    $perPage = 500;
    $offset = 0;

    while ($hasMore) {
        $url = "https://api.crowdin.com/api/v2/projects/{$projectId}/languages/" . str_replace('_', '-', $locale) . "/translations?fileId={$fileId}&offset={$offset}&limit={$perPage}";
        $response = json_decode(file_get_contents($url, false, stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer {$crowdinToken}\r\n"
            ]
        ])), true);

        foreach ($response['data'] as $translation) {
            $data = $translation['data']['user'];
            $translators[$data['id']] = [
                'name' => $data['fullName'],
                'avatarUrl' => $data['avatarUrl']
            ];
        }

        $hasMore = count($response['data']) >= $perPage;
        $offset += $perPage;
    }

    $fileTranslators[$fileId] = $translators;
    return $translators;
}

function getPercentageTranslated(Translations $translations): float
{
    $total = 0;
    $translated = 0;

    foreach ($translations as $translation) {
        if (! empty($translation->getTranslation())) {
            $translated++;
        }
        $total++;
    }

    return round($translated / $total * 100, 2);
}

foreach (glob('docs/*') as $sourceDir) {
    $version = basename($sourceDir);
    $targetDir = "build/{$locale}/{$version}";
    $poDir = "po/{$locale}";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    render(<<<HTML
        <div class="py-1">
            <span class="text-blue-500 px-1">→</span>
            <span>Processing version <strong>{$version}</strong>...</span>
        </div>
        HTML);

    $sourceFiles = new Finder()->in($sourceDir)->files()->name('*.md');

    foreach ($sourceFiles as $file) {
        $path = str_replace($sourceDir, '', $file->getPath());
        $targetPath = "{$targetDir}/{$path}";
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
        }
        copy($file, "{$targetPath}/" . basename($file));
    }

    $targetFiles = new Finder()->in($targetDir)->files()->name('*.md')->sortByName();
    foreach ($targetFiles as $md) {
        $mdFilename = str_replace([$targetDir . '/', '.md'], '', $md->getFilename());

        if (! file_exists("{$poDir}/{$mdFilename}.po")) {
            continue;
        }

        render(<<<HTML
            <div class="pl-2">
                <span class="text-blue-500 px-1">→</span>
                <span>Building <strong>{$targetDir}/{$mdFilename}.md</strong>...</span>
            </div>
            HTML);

        $poFile = "{$poDir}/{$mdFilename}.po";
        $translations = new PoLoader()->loadFile($poFile);
        $metadata = $translations->getHeaders();
        $fileId = $metadata->get('X-Crowdin-File-ID') ?? null;
        $projectId = $metadata->get('X-Crowdin-Project-ID') ?? null;
        $updatedAt = $metadata->get('PO-Revision-Date') ?? null;

        $babelaFishoProcess = new Process([
            'bin/babela-fisho',
            'to-md',
            "{$sourceDir}/{$mdFilename}.md",
            $poFile,
        ]);
        $babelaFishoProcess->run();

        $output = $babelaFishoProcess->getOutput();

        $frontMatters = [];
        if ($fileId !== null) {
            $frontMatters['crowdinUrl'] = "https://crowdin.com/translate/laravel-docs/{$fileId}/en-" . strtolower(str_replace('_', '', $locale));
            $frontMatters['updatedAt'] = str_replace(' ', 'T', $updatedAt) . ":00Z";
            $frontMatters['contributors'] = listFileTranslators($projectId, $fileId, $locale, $crowdinToken);
            $frontMatters['progress'] = getPercentageTranslated($translations);
        }
        file_put_contents("{$targetDir}/{$mdFilename}.md", "---\n" . Yaml::dump($frontMatters) . "---\n\n" . $output);

        render(<<<HTML
            <div class="pl-4">
                <span class="text-green px-1">✓</span>
                <span>Succeeded building <strong>{$targetDir}/{$mdFilename}.md</strong></span>
            </div>
            HTML);
    }
}

render(<<<'HTML'
    <div class="py-1">
        <span class="text-green px-1">✓</span>
        <span>Build completed</span>
    </div>
    HTML);
