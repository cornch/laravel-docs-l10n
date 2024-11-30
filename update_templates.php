<?php

// translated from following pythong script:
// import os
//import re
//from glob import glob
//from mdpo.md2po import markdown_to_pofile
//
//def filter_anchors(self, msgid, *args):
//  if re.match(r'<a name="(.+?)"></a>', msgid) is not None:
//    self.disable_next_block = True
//
//print('Remove old pot files...')
//for file in glob('templates/*', recursive=True):
//  if not os.path.isdir(file):
//    os.remove(file)
//
//mdFiles = set([
//  re.match(r'docs/[^/]+?/(.+\.md)', file).group(1)
//  for file in glob('docs/**/*.md')
//])
//
//for file in mdFiles:
//  if file == 'readme.md' or file == 'license.md':
//    continue
//
//  print(f'Processing {file}')
//
//  pot_path = f'templates/{file[:-3]}.pot'
//
//  os.makedirs(os.path.dirname(pot_path), exist_ok=True)
//
//  markdown_to_pofile(
//    f'docs/**/{file}',
//    po_filepath=pot_path,
//    wrapwidth=0,
//    save=True,
//    include_codeblocks=True,
//    events={ 'msgid': filter_anchors }
//  )

// The markdown_to_pofile should be replaced with a call to `babela-fisho` binary invoke

use Gettext\Generator\PoGenerator;
use Gettext\Loader\PoLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use function Termwind\render;

require __DIR__ . '/vendor/autoload.php';

$files= new Finder()->files()->in(__DIR__ . '/docs/');

if (! $files->hasResults()) {
    render(<<<'HTML'
        <div class="py-1">
            <span class="text-red-500 px-1 mr-1">ERROR</span>
            <span>There are no markdown files to process.</span>
        </div>
        HTML);
}

$markdownFiles = [];

foreach ($files as $file) {
    $markdownFilename = $file->getFilename();

    if (in_array($markdownFilename, ['readme.md', 'license.md'])) {
        continue;
    }

    if (! array_key_exists($markdownFilename, $markdownFiles)) {
        $markdownFiles[$markdownFilename] = [];
    }

    $markdownFiles[$markdownFilename][] = $file->getRelativePathname();
}

// sort by versions:
// 8.x => 9.x => 10.x => 11.x => 12.x => 13.x => 14.x...
// master should be the last one
foreach ($markdownFiles as $filename => $files) {
    usort($files, function ($a, $b) {
        $a = explode('/', $a, 2)[0];
        $b = explode('/', $b, 2)[0];

        if ($a === 'master') {
            return 1;
        }

        if ($b === 'master') {
            return -1;
        }

        return version_compare($a, $b);
    });

    $markdownFiles[$filename] = $files;
}

ksort($markdownFiles);

$poLoader = new PoLoader();
$poGenerator = new PoGenerator();

foreach ($markdownFiles as $filename => $files) {
    $potPath = __DIR__ . '/templates/' . pathinfo($filename, PATHINFO_FILENAME) . '.pot';

    if (! is_dir(dirname($potPath))) {
        mkdir(dirname($potPath), 0755, true);
    }

    $process = new Process([
        __DIR__ . '/bin/babela-fisho',
        'to-po',
        ...array_map(
            fn ($file) => './docs/' . $file,
            $files
        ),
    ]);

    $process->run();

    if (! $process->isSuccessful()) {
        render(<<<'HTML'
            <div class="py-1">
                <span class="text-red-500 px-1 mr-1">ERROR</span>
                <span>Failed to process the markdown files.</span>
            </div>
            HTML);
    }

    $potFileContent = $process->getOutput();

    // filter out anchors
    /** @var \Gettext\Translations|\Gettext\Translation[] $translations */
    $translations = $poLoader->loadString($potFileContent);
    foreach ($translations as $translation) {
        if (preg_match('/<a name="(.+?)"><\/a>/', $translation->getOriginal())) {
            $translation->disable(true);
        }
    }

    $poContent = $poGenerator->generateString($translations);

    // remove all msgctxt
    $poContent = preg_replace('/^msgctxt .+\n/m', '', $poContent);

    file_put_contents($potPath, $poContent);

    render(<<<HTML
        <div>
            <span class="px-1 text-green">✓</span>
            <span>Processed <strong>{$filename}</strong></span>
        </div>
        HTML);
}

render(<<<'HTML'
    <div class="py-1">
        <span class="px-1 text-green">✓</span>
        <span>All markdown files have been processed.</span>
    </div>
    HTML);
