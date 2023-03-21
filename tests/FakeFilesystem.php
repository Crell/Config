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
                'yaml' => [
                    'crell_config_sample.yaml'    => <<<END
                        int: 5
                        END,
                ],
                'json' => [
                    'crell_config_sample.json'    => <<<END
                        {"int": 5}
                        END,
                ],
                'ini' => [
                    'crell_config_sample.ini'    => <<<END
                        int = 5
                        END,
                ],

                // For simulationg multi-environment laoding.
                'dev' => [
                    'crell_config_configobjects_sample.yaml'    => <<<END
                        int: 5
                        END,
                ],
                'base' => [
                    'crell_config_configobjects_sample.yaml'    => <<<END
                        string: 'beep'
                        float: 3.14
                        int: 3
                        END,
                ],
            ],
        ];
    }
}
