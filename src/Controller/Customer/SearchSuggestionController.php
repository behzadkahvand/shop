<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Service\SearchSuggestion\SearchSuggestionAdapter;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/search-suggestion", name: "search-suggestion.")]
class SearchSuggestionController extends Controller
{
    /**
     * @OA\Tag(name="search-suggestion")
     * @OA\Parameter(
     *     name="searchQuery",
     *     in="query",
     *     description="it's query string of search and must be at least 3 characters, for example:
     *         searchQuery=دیج"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of categories or products that are like to the given query string",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(
     *            property="categories",
     *            type="array",
     *            @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="code", type="string"),
     *             )
     *            )
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/suggest", name: "suggest", methods: ["GET"])]
    public function suggestion(Request $request, SearchSuggestionAdapter $searchSuggestionAdapter): JsonResponse
    {
        $searchQuery = $request->query->get('searchQuery');

        if (!$searchQuery || mb_strlen($searchQuery, 'UTF-8') < 3) {
            return $this->setMessage("Search query must exists and at least has 3 characters")
                        ->respond(statusCode: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->respond(
            $searchSuggestionAdapter->suggest($searchQuery),
            context: ['groups' => ['search.suggestion']]
        );
    }
}
