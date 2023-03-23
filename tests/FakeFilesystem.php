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

                // For simulationg multi-environment loading.
                'dev' => [
                    'crell_config_configobjects_sample.yaml'    => <<<END
                        int: 5
                        END,
                    'crell_config_configobjects_complex.yaml'    => <<<END
                        sample:
                            float: 2.3
                            string: val
                        anotherString: value
                        db:
                            db_port: 2000
                        END,
                    'myname.yaml' => <<<END
                        things: bar
                        END,
                ],
                'base' => [
                    'crell_config_configobjects_sample.yaml'    => <<<END
                        string: 'beep'
                        float: 3.14
                        int: 3
                        END,
                    'crell_config_configobjects_complex.yaml'    => <<<END
                        sample:
                            int: 3
                        db:
                            db_name: name
                            db_host: host
                            db_user: user
                            db_pass: pass
                            db_port: 1000
                        END,
                    'myname.yaml' => <<<END
                        stuff: foo
                        END,
                ],
            ],
        ];
    }
}
