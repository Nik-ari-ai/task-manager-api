<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends ApiTestCase
{
    public function testCreateTaskReturnsCreatedWithDefaultStatus(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Prepare report', 'description' => 'Sales report for May'])
        );

        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertArrayHasKey('id', $data);
        self::assertSame('Prepare report', $data['title']);
        self::assertSame('Sales report for May', $data['description']);
        self::assertSame('new', $data['status']['name']);
        self::assertArrayHasKey('created_at', $data);
        self::assertArrayHasKey('updated_at', $data);
    }

    public function testCreateTaskWithoutDescriptionStoresNull(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Task without description'])
        );

        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertNull($data['description']);
    }

    public function testCreateTaskWithBlankTitleReturnsUnprocessableEntity(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => ''])
        );

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertArrayHasKey('details', $data);
        self::assertArrayHasKey('violations', $data['details']);
    }

    public function testCreateTaskWithNonStringTitleReturnsUnprocessableEntity(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 12345])
        );

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateTaskWithMalformedJsonReturnsBadRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title": "broken"'
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateTaskWithJsonArrayRootReturnsBadRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['one', 'two'])
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateTaskWithEmptyJsonArrayRootReturnsBadRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '[]'
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateTaskWithEmptyBodyReturnsBadRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            ''
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateTaskWithJsonStringRootReturnsBadRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '"just a string"'
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateStatusWithMalformedJsonReturnsBadRequest(): void
    {
        $taskId = $this->createTaskThroughApi('Task with broken patch');

        $this->client->request(
            'PATCH',
            sprintf('/api/tasks/%d/status', $taskId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"status": "done"'
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteMissingTaskReturnsNotFound(): void
    {
        $this->client->request('DELETE', '/api/tasks/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testListTasksReturnsOk(): void
    {
        $this->createTaskThroughApi('First task');
        $this->createTaskThroughApi('Second task');

        $this->client->request('GET', '/api/tasks');

        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertCount(2, $data);
    }

    public function testListTasksFilteredByExistingStatusReturnsOk(): void
    {
        $this->createTaskThroughApi('First task');

        $this->client->request('GET', '/api/tasks?status=new');

        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertCount(1, $data);
        self::assertSame('new', $data[0]['status']['name']);
    }

    public function testListTasksFilteredByExistingStatusWithoutMatchesReturnsEmptyList(): void
    {
        $this->createTaskThroughApi('First task');

        $this->client->request('GET', '/api/tasks?status=done');

        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertCount(0, $data);
    }

    public function testListTasksFilteredByUnknownStatusReturnsNotFound(): void
    {
        $this->client->request('GET', '/api/tasks?status=does_not_exist');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testGetMissingTaskReturnsNotFound(): void
    {
        $this->client->request('GET', '/api/tasks/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateStatusReturnsOkAndChangesUpdatedAt(): void
    {
        $taskId = $this->createTaskThroughApi('Task to move');

        $originalUpdatedAt = $this->readTaskUpdatedAt($taskId);

        sleep(1);

        $this->client->request(
            'PATCH',
            sprintf('/api/tasks/%d/status', $taskId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'done'])
        );

        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertSame('done', $data['status']['name']);

        $updatedUpdatedAt = $this->readTaskUpdatedAt($taskId);

        self::assertNotSame(
            $originalUpdatedAt->format(\DateTimeInterface::ATOM),
            $updatedUpdatedAt->format(\DateTimeInterface::ATOM)
        );
    }

    public function testUpdateStatusWithUnknownStatusReturnsUnprocessableEntity(): void
    {
        $taskId = $this->createTaskThroughApi('Task with bad status');

        $this->client->request(
            'PATCH',
            sprintf('/api/tasks/%d/status', $taskId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'does_not_exist'])
        );

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateStatusOnMissingTaskReturnsNotFound(): void
    {
        $this->client->request(
            'PATCH',
            '/api/tasks/999999/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'done'])
        );

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteTaskReturnsNoContent(): void
    {
        $taskId = $this->createTaskThroughApi('Task to delete');

        $this->client->request('DELETE', sprintf('/api/tasks/%d', $taskId));

        self::assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', sprintf('/api/tasks/%d', $taskId));

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    private function createTaskThroughApi(string $title): int
    {
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => $title])
        );

        $data = $this->decodeResponse();

        return (int) $data['id'];
    }

    private function readTaskUpdatedAt(int $taskId): \DateTimeImmutable
    {
        $this->entityManager->clear();

        $task = $this->entityManager->find(Task::class, $taskId);

        self::assertInstanceOf(Task::class, $task);

        return $task->getUpdatedAt();
    }
}
