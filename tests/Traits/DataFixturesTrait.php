<?php

namespace App\Tests\Traits;

trait DataFixturesTrait
{
    private function getFixtures(): array
    {
        $mainNamespace    = "App\DataFixtures";
        $projectDirectory = $this->client->getContainer()->getParameter('kernel.project_dir');
        $files            = glob($projectDirectory . '/src/DataFixtures/*.php');
        $result           = [];
        foreach ($files as $file) {
            $slices                   = explode('/', $file);
            $fileName                 = end($slices);
            $fileNameWithoutExtension = rtrim($fileName, '.php');

            if ($fileNameWithoutExtension !== 'BaseFixture') {
                $result[] = $mainNamespace . '\\' . $fileNameWithoutExtension;
            }
        }

        return $result;
    }
}
