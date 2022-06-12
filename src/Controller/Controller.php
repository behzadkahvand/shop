<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Seller;
use App\Service\Utils\Error\ErrorExtractor;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @method Admin|Customer|Seller getUser()
 */
abstract class Controller extends AbstractController
{
    private array $metas = [];

    private string $message = 'Response successfully returned';

    private bool $hasErrors = false;

    private PaginatorInterface $paginator;

    /**
     * @return PaginatorInterface
     */
    public function getPaginator(): PaginatorInterface
    {
        return $this->paginator;
    }

    /**
     * @required
     *
     * @param PaginatorInterface $paginator
     *
     * @return Controller
     */
    public function setPaginator(PaginatorInterface $paginator): self
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * @return array
     */
    public function getMetas(): array
    {
        return $this->metas;
    }

    /**
     * @param array $metas
     */
    public function setMetas(array $metas): self
    {
        $this->metas = $metas;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    /**
     * @param bool $hasErrors
     */
    public function setHasErrors(bool $hasErrors): self
    {
        $this->hasErrors = $hasErrors;

        return $this;
    }

    /**
     * Sends serialized json response with default serialization group to client.
     *
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     * @param array $context
     *
     * @return JsonResponse
     */
    protected function respond(
        $data = [],
        int $statusCode = Response::HTTP_OK,
        array $headers = [],
        array $context = ['groups' => 'default']
    ): JsonResponse {
        return $this->json(
            [
                'succeed' => ! $this->hasErrors(),
                'message' => $this->getMessage(),
                'results' => $data,
                'metas' => $this->getMetas(),
            ],
            $statusCode,
            $headers,
            array_merge(
                $context,
                [
                    AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                    'circular_reference_handler' => function ($object) {
                        return (array)$object;
                    },
                ]
            )
        );
    }

    /**
     * Paginates anything (depending on event listeners)
     * into Pagination object, which is a view targeted
     * pagination object (might be aggregated helper object)
     * responsible for the pagination result representation.
     *
     * @param mixed $target - anything what needs to be paginated
     * @param array $options - less used options:
     *                       boolean $distinct - default true for distinction of results
     *                       string $alias - pagination alias, default none
     *                       array $whitelist - sortable whitelist for target fields being paginated
     *
     * @param int $statusCode
     * @param array $headers
     * @param array $context
     * @param array $meta
     *
     * @return JsonResponse
     */
    public function respondWithPagination(
        $target,
        array $options = [],
        int $statusCode = Response::HTTP_OK,
        array $headers = [],
        array $context = ['groups' => ['default']],
        array $meta = []
    ): JsonResponse {
        $request = $this->get('request_stack')->getMainRequest();
        $pagination = $this->getPaginator()->paginate(
            $target,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10),
            $options
        );

        return $this->setMetas(
            array_merge([
                'page' => $pagination->getCurrentPageNumber(),
                'perPage' => $pagination->getItemNumberPerPage(),
                'totalItems' => $pagination->getTotalItemCount(),
                'totalPages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
            ], $meta)
        )->respond($pagination->getItems(), $statusCode, $headers, $context);
    }

    /**
     * Sends a response with error.
     *
     * @param string message
     * @param array $constraints
     * @param int $status
     *
     * @return JsonResponse
     */
    protected function respondWithError(
        string $message = 'Error has been detected!',
        array $constraints = [],
        int $status = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        return $this->setHasErrors(true)
            ->setMessage($message)
            ->respond($constraints, $status);
    }

    /**
     * Responds validation error with messages.
     *
     * @param FormInterface $form
     * @param bool $flatten
     *
     * @return JsonResponse
     *
     * @throws ExceptionInterface
     */
    protected function respondValidatorFailed(FormInterface $form, $flatten = true): JsonResponse
    {
        if ($flatten) {
            $errors = [];
            foreach ($form->getErrors(true, true) as $error) {
                $errors[$error->getOrigin()->getName()][] = $error->getMessage();
            }
        } else {
            $errors = $this->get('serializer')->normalize($form);
        }

        return $this->respondWithError(
            'Validation error has been detected!',
            $errors,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Sends a response that the object has been deleted, and also indicates
     * the id of the object that has been deleted.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    protected function respondEntityRemoved(int $id): JsonResponse
    {
        return $this->setMessage('Entity has been removed successfully!')->respond(['id' => $id]);
    }

    /**
     * Sends an error when the query didn't have the right parameters for
     * creating an object.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondNotTheRightParameters($message = 'Wrong parameter has been detected!'): JsonResponse
    {
        return $this->respondWithError($message, [], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Sends a response invalid query (http 500) to the request.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondInvalidQuery($message = 'Invalid query has been detected!'): JsonResponse
    {
        return $this->respondWithError($message, [], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Sends an error when the query contains invalid parameters.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondInvalidParameters($message = 'Invalid parameters has been detected!'): JsonResponse
    {
        return $this->respondWithError($message, [], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Sends a response unauthorized (401) to the request.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondUnauthorized($message = 'Unauthorized action has been detected!'): JsonResponse
    {
        return $this->respondWithError($message, [], Response::HTTP_UNAUTHORIZED);
    }

    public function respondValidationViolation(ConstraintViolationListInterface $constraintViolationList): JsonResponse
    {
        $errors = $this->get('error_extractor')->extract($constraintViolationList);

        return $this->respondWithError(
            'Validation error has been detected!',
            $errors,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'error_extractor' => ErrorExtractor::class,
            ]
        );
    }
}
