<?php

namespace App\Tests\Controller\Seller;

use App\Faker\Provider\CustomImageProvider;
use App\Tests\Controller\BaseControllerTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MediaControllerTest extends BaseControllerTestCase
{
    private bool $customImageProviderIsLoaded = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (false === $this->customImageProviderIsLoaded) {
            $this->faker->addProvider(new CustomImageProvider($this->faker));
            $this->customImageProviderIsLoaded = true;
        }
    }

    public function testUploadInvalidMediaType(): void
    {
        $this->loginAs($this->seller)->sendRequest('POST', '/seller/media/invalid/upload');

        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testUploadInvalidMediaSize(): void
    {
        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $image = $this->faker->customImage(400, 400);

        if (false === $image) {
            $this->addWarning('Unable to create custom image. probably gd extension is not loaded.');

            return;
        }

        $this->client->request('POST', '/seller/media/product-gallery/upload', [], [
            'imageFile' => new UploadedFile($image, 'category-image.jpg', null, null, true),
        ], [
            'CONTENT_TYPE' => 'application/json',
            'ACCEPT' => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        $this->deleteUploadedFile($image);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertEquals(
            '{"succeed":false,"message":"Validation error has been detected!","results":{"imageFile":["The image width is too small (400px). Minimum width expected is 600px."]},"metas":[]}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testUploadInvalidProductFeaturedImageSize(): void
    {
        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $image = $this->faker->customImage(1000, 1000);

        if (false === $image) {
            $this->addWarning('Unable to create custom image. probably gd extension is not loaded.');

            return;
        }

        $this->client->request('POST', '/seller/media/product-image/upload', [], [
            'imageFile' => new UploadedFile($image, 'category-image.jpg', null, null, true),
        ], [
            'CONTENT_TYPE' => 'application/json',
            'ACCEPT' => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        $this->deleteUploadedFile($image);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertEquals(
            '{"succeed":false,"message":"Validation error has been detected!","results":{"imageFile":["The image width is too small (1000px). Minimum width expected is 1200px."]},"metas":[]}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testUpload(): void
    {
        $jwtToken = $this->getService(JWTTokenManagerInterface::class)->create($this->seller);
        $image = $this->faker->customImage(600, 600);

        if (false === $image) {
            $this->addWarning('Unable to create custom image. probably gd extension is not loaded.');

            return;
        }

        $this->client->request('POST', '/seller/media/product-gallery/upload', [], [
            'imageFile' => new UploadedFile($image, 'category-image.jpg', null, null, true),
        ], [
            'CONTENT_TYPE' => 'application/json',
            'ACCEPT' => 'application/json',
            'HTTP_Authorization' => "Bearer {$jwtToken}",
        ], null, true);

        $this->deleteUploadedFile($image);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertArrayHasKey('imageFileName', $response['results']);
        self::assertArrayHasKey('media', $response['results']);
        self::assertArrayHasKey('path', $response['results']['media']);
    }

    private function deleteUploadedFile(string $file): void
    {
        if (file_exists($file)) {
            exec('\\' === DIRECTORY_SEPARATOR ? "del {$file}" : "rm -rf {$file}");
        }
    }
}
