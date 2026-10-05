<?php

namespace App\Controller;

use App\Dto\CreateTaskRequest;
use App\Dto\UpdateTaskStatusRequest;
use App\OpenApi\TaskModel;
use App\Request\JsonRequestDecoder;
use App\Service\EntityPresenter;
use App\Service\TaskService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
#[OA\Tag(name: 'Tasks')]
class TaskController
{
    private TaskService $taskService;

    private EntityPresenter $presenter;

    private JsonRequestDecoder $jsonRequestDecoder;

    public function __construct(
        TaskService $taskService,
        EntityPresenter $presenter,
        JsonRequestDecoder $jsonRequestDecoder
    ) {
        $this->taskService = $taskService;
        $this->presenter = $presenter;
        $this->jsonRequestDecoder = $jsonRequestDecoder;
    }

    #[Route('', name: 'task_list', methods: ['GET'])]
    #[OA\Parameter(
        name: 'status',
        description: 'Filter tasks by the machine name of a status.',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'done')
    )]
    #[OA\Response(
        response: 200,
        description: 'The list of tasks.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TaskModel::class))
        )
    )]
    #[OA\Response(response: 404, description: 'The status used in the filter does not exist.')]
    public function list(Request $request): JsonResponse
    {
        $statusFilter = $request->query->get('status');

        if (null !== $statusFilter && '' !== $statusFilter) {
            $tasks = $this->taskService->getFilteredByStatusName((string) $statusFilter);
        } else {
            $tasks = $this->taskService->getAll();
        }

        return new JsonResponse($this->presenter->presentTaskList($tasks), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'task_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Response(
        response: 200,
        description: 'The requested task.',
        content: new OA\JsonContent(ref: new Model(type: TaskModel::class))
    )]
    #[OA\Response(response: 404, description: 'The task does not exist.')]
    public function get(int $id): JsonResponse
    {
        $task = $this->taskService->getById($id);

        return new JsonResponse($this->presenter->presentTask($task), Response::HTTP_OK);
    }

    #[Route('', name: 'task_create', methods: ['POST'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Prepare report'),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Sales report for May'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'The created task.',
        content: new OA\JsonContent(ref: new Model(type: TaskModel::class))
    )]
    #[OA\Response(response: 400, description: 'The request body is not a valid JSON object.')]
    #[OA\Response(response: 422, description: 'The input data failed validation.')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->jsonRequestDecoder->decodeAndValidate($request, CreateTaskRequest::class);

        $task = $this->taskService->create($dto);

        return new JsonResponse($this->presenter->presentTask($task), Response::HTTP_CREATED);
    }

    #[Route('/{id}/status', name: 'task_update_status', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['status'],
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'done'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'The task with the updated status.',
        content: new OA\JsonContent(ref: new Model(type: TaskModel::class))
    )]
    #[OA\Response(response: 400, description: 'The request body is not a valid JSON object.')]
    #[OA\Response(response: 404, description: 'The task does not exist.')]
    #[OA\Response(response: 422, description: 'The status value is invalid or cannot be applied.')]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $dto = $this->jsonRequestDecoder->decodeAndValidate($request, UpdateTaskStatusRequest::class);

        $task = $this->taskService->updateStatus($id, $dto);

        return new JsonResponse($this->presenter->presentTask($task), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'task_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Response(response: 204, description: 'The task was deleted.')]
    #[OA\Response(response: 404, description: 'The task does not exist.')]
    public function delete(int $id): JsonResponse
    {
        $this->taskService->delete($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
