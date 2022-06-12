<?php

namespace App\Tests\Controller\Seller;

use App\Entity\InventoryUpdateDemand;
use App\Entity\Seller;
use App\Messaging\Messages\Command\Product\InitializeInventoryUpdateDemand;
use App\Service\Product\Seller\InventoryUpdate\Creator\InventoryUpdateDemandCreator;
use App\Service\Product\Seller\InventoryUpdate\InventoryUpdateDemandInitializer;
use App\Tests\Controller\BaseControllerTestCase;
use DateTimeInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class InventoryUpdateDemandControllerTest extends BaseControllerTestCase
{
    public function testIndexSuccessfully(): void
    {
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);

        $demandCreator->create($this->seller);
        $demandCreator->create($this->seller);

        $this->manager->flush();

        $this->loginAs($this->seller)
             ->sendRequest('GET', '/seller/inventory-update-demands');

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('results', $result);
        self::assertCount(2, $result['results']);
    }

    public function testIndexSuccessfullyWithTrueOrderAndResponse(): void
    {
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);
        $demandCreator->create($this->seller);

        $demandTwo = $demandCreator->create($this->seller);
        $demandTwo->setFileName('demand_two.xlsx');

        $this->manager->flush();

        $this->loginAs($this->seller)
             ->sendRequest('GET', '/seller/inventory-update-demands');

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();
        self::assertArrayHasKey('results', $result);
        self::assertCount(2, $result['results']);

        self::assertEquals('demand_two.xlsx', $result['results'][0]['fileName']);
        self::assertEquals($demandTwo->getCreatedAt()->format('c'), $result['results'][0]['createdAt']);
        self::assertEquals($demandTwo->getStatus(), $result['results'][0]['status']);
    }

    public function testIndexSuccessfullyLoadOnlyDemandsBelongToCurrentSeller(): void
    {
        [$sellerOne, $sellerTwo] = $this->manager->getRepository(Seller::class)->findBy([], null, 2);

        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);
        $demandCreator->create($sellerOne);
        $demandCreator->create($sellerOne);
        $demandCreator->create($sellerTwo);

        $this->manager->flush();

        $this->loginAs($sellerOne)
             ->sendRequest('GET', '/seller/inventory-update-demands');

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();
        self::assertArrayHasKey('results', $result);
        self::assertCount(2, $result['results']);
    }

    public function testIndexSuccessfullyByFileName(): void
    {
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);

        $demandOne = $demandCreator->create($this->seller);
        $demandOne->setFileName('demand_one.xlsx');

        $demandTwo = $demandCreator->create($this->seller);
        $demandTwo->setFileName('demand_two.xlsx');

        $this->manager->flush();

        $this->loginAs($this->seller)
             ->sendRequest('GET', '/seller/inventory-update-demands?filter[fileName]=demand_two.xlsx');

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();
        self::assertArrayHasKey('results', $result);
        self::assertCount(1, $result['results']);
    }

    public function testSubmitSuccessfully(): void
    {
        $beforeCount = $this->entityCount(InventoryUpdateDemand::class);

        $this->loginAs($this->seller)
             ->sendRequest('POST', '/seller/inventory-update-demands');

        self::assertResponseIsSuccessful();
        self::assertEquals($beforeCount + 1, $this->entityCount(InventoryUpdateDemand::class));

        $inventoryUpdateDemand = $this->lastEntity(InventoryUpdateDemand::class);

        self::assertEquals('PENDING', $inventoryUpdateDemand->getStatus());
        self::assertEquals($this->seller->getId(), $inventoryUpdateDemand->getSeller()->getId());
        self::assertInstanceOf(DateTimeInterface::class, $inventoryUpdateDemand->getCreatedAt());
        self::assertInstanceOf(DateTimeInterface::class, $inventoryUpdateDemand->getUpdatedAt());
    }

    public function testDispatchMessageForInitializationAfterSubmitDemand(): void
    {
        $transport = $this->getService('messenger.transport.async');

        $transport->reset();

        $this->loginAs($this->seller)
             ->sendRequest('POST', '/seller/inventory-update-demands');

        $sentMessages = $transport->getSent();

        self::assertCount(1, $sentMessages);
        self::assertInstanceOf(
            InitializeInventoryUpdateDemand::class,
            $sentMessages[0]->getMessage()->getWrappedMessage()
        );
    }

    public function testSubmitSuccessfulWhenThereIsAPendingDemand(): void
    {
        $this->getService(InventoryUpdateDemandCreator::class)
             ->create($this->seller);

        $this->manager->flush();

        $beforeCount = $this->entityCount(InventoryUpdateDemand::class);

        $this->loginAs($this->seller)
             ->sendRequest('POST', '/seller/inventory-update-demands');

        self::assertResponseIsSuccessful();
        self::assertEquals($beforeCount + 1, $this->entityCount(InventoryUpdateDemand::class));
    }

    public function testDownloadSuccessfully(): void
    {
        /** @var InventoryUpdateDemandCreator $demandCreator */
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);
        $demand        = $demandCreator->create($this->seller);
        $this->manager->flush();

        /** @var InventoryUpdateDemandInitializer $initializer */
        $this->getService(InventoryUpdateDemandInitializer::class)
             ->initialize($demand);
        $this->manager->flush();

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('GET', '/seller/inventory-update-demands/' . $demand->getFileName(), [], [], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testDownloadRespondWithNotFoundWithWrongFilenameFormat(): void
    {
        /** @var InventoryUpdateDemandCreator $demandCreator */
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);
        $demand        = $demandCreator->create($this->seller);
        $this->manager->flush();

        /** @var InventoryUpdateDemandInitializer $initializer */
        $this->getService(InventoryUpdateDemandInitializer::class)
             ->initialize($demand);
        $this->manager->flush();

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('GET', '/seller/inventory-update-demands/' . 'wrong-file.txt', [], [], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testDownloadRespondWithNotFoundWithWrongFilename(): void
    {
        /** @var InventoryUpdateDemandCreator $demandCreator */
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);
        $demand        = $demandCreator->create($this->seller);
        $this->manager->flush();

        /** @var InventoryUpdateDemandInitializer $initializer */
        $this->getService(InventoryUpdateDemandInitializer::class)
             ->initialize($demand);
        $this->manager->flush();

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $this->client->request('GET', '/seller/inventory-update-demands/' . 'wrong-file.xlsx', [], [], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testDownloadRespondWithAccessDeniedWithFileBelongsToOtherSeller(): void
    {
        [$sellerOne, $sellerTwo] = $this->manager->getRepository(Seller::class)->findBy([], null, 2);
        /** @var InventoryUpdateDemandCreator $demandCreator */
        $demandCreator = $this->getService(InventoryUpdateDemandCreator::class);
        $demand        = $demandCreator->create($sellerOne);
        $this->manager->flush();

        /** @var InventoryUpdateDemandInitializer $initializer */
        $this->getService(InventoryUpdateDemandInitializer::class)
             ->initialize($demand);
        $this->manager->flush();

        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($sellerTwo);
        $this->client->request('GET', '/seller/inventory-update-demands/' . $demand->getFileName(), [], [], [
            'ACCEPT'             => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ]);

        self::assertResponseStatusCodeSame(403);
    }
}
