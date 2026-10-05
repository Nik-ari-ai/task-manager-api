<?php

namespace App\Controller;

use App\Dto\CreateStatusRequest;
use App\OpenApi\StatusModel;
use App\Request\JsonRequestDecoder;
use App\Service\EntityPresenter;
use App\Service\StatusService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/statuses')]
#[OA\Tag(name: 'Statuses')]
class StatusController
{
    private StatusService $statusService;

    private EntityPresenter $presenter;

    private JsonRequestDecoder $jsonRequestDecoder;

    public function __construct(
        StatusService $statusService,
        EntityPresenter $presenter,
        JsonRequestDecoder $jsonRequestDecoder
    ) {
        $this->statusService = $statusService;
        $this->presenter = $presenter;
        $this->jsonRequestDecoder = $jsonRequestDecoder;
    }

    #[Route('', name: 'status_list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'The list of statuses.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: StatusModel::class))
        )
    )]
    public function list(): JsonResponse
    {
        $statuses = $this->statusService->getAll();

        return new JsonResponse($this->presenter->presentStatusList($statuses), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'status_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Response(
        response: 200,
        description: 'The requested status.',
        content: new OA\JsonContent(ref: new Model(type: StatusModel::class))
    )]
    #[OA\Response(response: 404, description: 'The status does not exist.')]
    public function get(int $id): JsonResponse
    {
        $status = $this->statusService->getById($id);

        return new JsonResponse($this->presenter->presentStatus($status), Response::HTTP_OK);
    }

    #[Route('', name: 'status_create', methods: ['POST'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'title'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'code_review'),
                new OA\Property(property: 'title', type: 'string', example: 'Code review'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'The created status.',
        content: new OA\JsonContent(ref: new Model(type: StatusModel::class))
    )]
    #[OA\Response(response: 400, description: 'The request body is not a valid JSON object.')]
    #[OA\Response(response: 409, description: 'A status with this name already exists.')]
    #[OA\Response(response: 422, description: 'The input data failed validation.')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->jsonRequestDecoder->decodeAndValidate($request, CreateStatusRequest::class);

        $status = $this->statusService->create($dto);

        return new JsonResponse($this->presenter->presentStatus($status), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'status_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Response(response: 204, description: 'The status was deleted.')]
    #[OA\Response(response: 404, description: 'The status does not exist.')]
    #[OA\Response(response: 409, description: 'The status is a system status or is used by tasks.')]
    public function delete(int $id): JsonResponse
    {
        $this->statusService->delete($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
