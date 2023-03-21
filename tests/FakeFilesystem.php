<?php

declare(strict_types=1);

namespace Crell\Config;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;

trait FakeFilesystem
{
    private vfsStreamDirectory $root;

    private vfsStreamContent $dataDir;

    protected function setupFilesystem(): void
    {
        $this->root = vfsStream::setup('root', null, $this->getStructure());
        $this->dataDir = $this->root->getChild('data');
    }

    protected function getStructure(): array
    {
        return [
            'data' => [
                'empty' => [],
                'php' => [
                    'crell_config_sample.php'    => <<<END
                        <?php return ['int' => 5];
                        END,
                ],
            ],
        ];
    }
}
