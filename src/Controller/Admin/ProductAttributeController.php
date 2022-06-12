<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\DTO\Admin\AttributeValueData;
use App\DTO\Admin\ProductAttributeData;
use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Form\Type\Admin\ProductAttributeType;
use App\Repository\CategoryAttributeRepository;
use App\Repository\ProductAttributeRepository;
use App\Service\ProductAttribute\DTO\ProductAttributeTemplateData;
use App\Service\ProductAttribute\Exceptions\RequiredFieldIsNotSetException;
use App\Service\ProductAttribute\ProductAttributeService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\CategoryAttribute;
use Nelmio\ApiDocBundle\Annotation\Model;
use Throwable;

#[Route("/products/{id}/attributes", name: "product.attribute.", requirements: ["id" => "\d+"])]
class ProductAttributeController extends Controller
{
    /**
     * @OA\Tag(name="Product Attribute")
     * @OA\Response(
     *     response=200,
     *     description="Show product attributes",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ProductAttributeTemplateData::class, groups={"product.attribute.read"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "show", methods: ["GET"])]
    public function show(Product $product, CategoryAttributeRepository $categoryAttributeRepository): JsonResponse
    {
        $data = $categoryAttributeRepository->getCategoryTemplateWithProductAttributeValues(
            $product->getCategory(),
            $product
        );

        $result = [];
        /** @var CategoryAttribute $categoryAttribute */
        foreach ($data as $categoryAttribute) {
            array_push($result, (new ProductAttributeTemplateData())
                ->setAttribute($categoryAttribute->getAttribute())
                ->setIsRequired($categoryAttribute->getIsRequired())
                ->setProductAttribute($categoryAttribute->getAttribute()->getProductAttributes()->first() ?: null));
        }

        return $this->respond($result, context: ['groups' => ['product.attribute.read']]);
    }

    /**
     * @OA\Tag(name="Product Attribute")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *              property="attributes",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="attribute", type="integer"),
     *                  @OA\Property(property="value", type="string")
     *              )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update product attributes",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ProductAttribute::class, groups={"product.attribute.update"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "update", methods: ["POST"])]
    public function update(
        Request $request,
        Product $product,
        ProductAttributeService $productAttributeService
    ): JsonResponse {
        $form = $this->createForm(
            ProductAttributeType::class,
            options: ['validation_groups' => 'product.attribute.update',]
        );
        $form->submit($request->request->all());

        if ($form->isValid()) {
            /** @var ProductAttributeData $formData */
            $formData   = $form->getData();
            $attributes = $formData->getAttributes();
            try {
                $productAttributeService->updateProductAttributes($product, $attributes);
            } catch (RequiredFieldIsNotSetException $exception) {
                return $this->respondInvalidParameters($exception->getMessage());
            } catch (Throwable $exception) {
                return $this->respondWithError();
            }

            return $this->respond($product->getAttributes(), context: ['groups' => 'product.attribute.update']);
        }

        return $this->respondValidatorFailed($form);
    }
}
