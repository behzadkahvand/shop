<?php

namespace App\Tests\Controller\Seller;

use App\Entity\InventoryUpdateDemand;
use App\Entity\InventoryUpdateSheet;
use App\Entity\Seller;
use App\Messaging\Messages\Command\Product\ProcessInventoryUpdateSheet;
use App\Service\Product\Seller\InventoryUpdate\Creator\InventoryUpdateDemandCreator;
use App\Service\Product\Seller\InventoryUpdate\Creator\InventoryUpdateSheetCreator;
use App\Service\Product\Seller\InventoryUpdate\InventoryUpdateDemandInitializer;
use App\Service\Product\Seller\InventoryUpdate\InventoryUpdateSheetManager;
use App\Tests\Controller\BaseControllerTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class InventoryUpdateSheetControllerTest extends BaseControllerTestCase
{
    public function testUploadSuccessfully(): void
    {
        /** @var InventoryUpdateDemandCreator $demandCreator */
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);
        $demand        = $demandCreator->create($this->seller);
        $this->manager->flush();

        /** @var InventoryUpdateDemandInitializer $initializer */
        $this->getService(InventoryUpdateDemandInitializer::class)
             ->initialize($demand);

        $this->manager->flush();

        $beforeCount = $this->entityCount(InventoryUpdateSheet::class);
        $jwtToken    = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);

        $this->client->request('POST', '/seller/inventory-update-sheets', [], [
            'sheetFile' => new UploadedFile(
                $this->getService(InventoryUpdateSheetManager::class)->getInitializedSheetPath($demand),
                $demand->getFileName(),
                null,
                null,
                true
            ),
        ], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        self::assertResponseIsSuccessful();

        self::assertEquals($beforeCount + 1, $this->entityCount(InventoryUpdateSheet::class));

        $sheet = $this->lastEntity(InventoryUpdateSheet::class);
        self::assertNotNull($sheet->getDirPath());
        self::assertEquals($demand->getFileName(), $sheet->getFileName());

        $result = $this->getControllerResponse();
        self::assertArrayHasKey('results', $result);

        $reader = new Xlsx();
        self::assertTrue(
            $reader->canRead(
                $this->client->getContainer()->getParameter('kernel.project_dir') .
                '/var/storage/' .
                $this->client->getContainer()->getParameter('kernel.environment') .
                '/' .
                $sheet->getDirPath() .
                '/' .
                $sheet->getFileName()
            )
        );
    }

    public function testUploadFailsForWrongFileType(): void
    {
        $txtFile = new UploadedFile(
            __DIR__ . '/../../../src/DataFixtures/csv/sample_text_file.txt',
            'sample_text_file.txt',
            'text/txt',
            null,
            true
        );

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('POST', '/seller/inventory-update-sheets', [], [
            'sheetFile' => $txtFile,
        ], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        self::assertResponseStatusCodeSame(422);

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('sheetFile', $response['results']);
    }

    public function testUploadFailsForExcelFileWithWrongName(): void
    {
        $demand = $this->getService(InventoryUpdateDemandCreator::class)->create($this->seller);

        $this->manager->flush();

        $this->getService(InventoryUpdateDemandInitializer::class)->initialize($demand);

        $this->manager->flush();

        $pathInfo = pathinfo($this->getService(InventoryUpdateSheetManager::class)->getInitializedSheetPath($demand));

        copy($pathInfo['dirname'] . '/' . $pathInfo['basename'], $pathInfo['dirname'] . '/wrong-name.xlsx');

        $file = new UploadedFile($pathInfo['dirname'] . '/wrong-name.xlsx', 'wrong-name.xlsx', null, null, true);

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('POST', '/seller/inventory-update-sheets', [], [
            'sheetFile' => $file,
        ], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        self::assertResponseStatusCodeSame(422);

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('sheetFile', $response['results']);
        self::assertEquals('درخواست مرتبط با فایل "wrong-name.xlsx" پیدا نشد.', $response['results']['sheetFile'][0]);
    }

    public function testUploadFailsForExcelFileThatExpired(): void
    {
        $demand = $this->getService(InventoryUpdateDemandCreator::class)
                       ->create($this->seller);

        $this->manager->flush();

        $this->getService(InventoryUpdateDemandInitializer::class)
             ->initialize($demand);

        $demand->setExpiresAt(new \DateTime('-1 day'));

        $this->manager->flush();

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('POST', '/seller/inventory-update-sheets', [], [
            'sheetFile' => $this->copyDemandFile($demand),
        ], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        self::assertResponseStatusCodeSame(422);

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('sheetFile', $response['results']);
        self::assertEquals('فایل آپلود شده منقضی شده است.', $response['results']['sheetFile'][0]);
    }

    public function testUploadFailsForExcelFileThatAlreadyUploaded(): void
    {
        $demand = $this->getService(InventoryUpdateDemandCreator::class)
                       ->create($this->seller);

        $this->manager->flush();

        $this->getService(InventoryUpdateDemandInitializer::class)
             ->initialize($demand);

        $demand->setExpiresAt(new \DateTime('+1 day'));

        $this->manager->flush();

        $this->getService(InventoryUpdateSheetCreator::class)->create($this->copyDemandFile($demand));
        $this->manager->flush();

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('POST', '/seller/inventory-update-sheets', [], [
            'sheetFile' => $this->copyDemandFile($demand),
        ], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        self::assertResponseStatusCodeSame(422);

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('sheetFile', $response['results']);
        self::assertEquals('فایل آپلود شده قبلا پردازش شده است.', $response['results']['sheetFile'][0]);
    }

    public function testUploadFailsForExcelFileThatBelongsOtherSeller(): void
    {
        $sellers       = $this->manager->getRepository(Seller::class)->findBy([], null, 2);
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);
        $demand        = $demandCreator->create($sellers[0]);

        $this->manager->flush();

        $this->getService(InventoryUpdateDemandInitializer::class)->initialize($demand);

        $demand->setExpiresAt(new \DateTime('-1 day'));

        $this->manager->flush();

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($sellers[1]);
        $this->client->request('POST', '/seller/inventory-update-sheets', [], [
            'sheetFile' => $this->copyDemandFile($demand),
        ], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        self::assertResponseStatusCodeSame(422);

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('sheetFile', $response['results']);
        self::assertEquals("درخواست مرتبط با فایل \"{$demand->getFileName()}\" پیدا نشد.", $response['results']['sheetFile'][0]);
    }

    public function testDispatchMessageToProcessUploadedFile(): void
    {
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $demand = $this->getService(InventoryUpdateDemandCreator::class)->create($this->seller);

        $this->manager->flush();

        $this->getService(InventoryUpdateDemandInitializer::class)->initialize($demand);

        $this->manager->flush();

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('POST', '/seller/inventory-update-sheets', [], [
            'sheetFile' => $this->copyDemandFile($demand),
        ], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        $sentMessages = $transport->getSent();

        self::assertCount(1, $sentMessages);
        self::assertInstanceOf(
            ProcessInventoryUpdateSheet::class,
            $sentMessages[0]->getMessage()->getWrappedMessage()
        );
    }

    public function testUploadFailsIfSheetFileIsNull(): void
    {
        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('POST', '/seller/inventory-update-sheets', [], [
            'sheetFile' => [],
        ], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        self::assertResponseStatusCodeSame(422);

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('sheetFile', $response['results']);
        self::assertEquals('This value should not be null.', $response['results']['sheetFile'][0]);
    }

    private function copyDemandFile(InventoryUpdateDemand $demand)
    {
        $targetFilePath  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $demand->getFileName();
        $initializedPath = $this->getService(InventoryUpdateSheetManager::class)->getInitializedSheetPath($demand);

        copy($initializedPath, $targetFilePath);

        return new UploadedFile(
            $targetFilePath,
            $demand->getFileName(),
            null,
            null,
            true
        );
    }
}
