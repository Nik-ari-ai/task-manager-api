<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class StatusControllerTest extends ApiTestCase
{
    public function testListStatusesReturnsDefaults(): void
    {
        $this->client->request('GET', '/api/statuses');

        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertCount(3, $data);
    }

    public function testGetStatusReturnsOk(): void
    {
        $statusId = $this->findStatusIdByName('new');

        $this->client->request('GET', sprintf('/api/statuses/%d', $statusId));

        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertSame('new', $data['name']);
    }

    public function testGetMissingStatusReturnsNotFound(): void
    {
        $this->client->request('GET', '/api/statuses/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateStatusReturnsCreated(): void
    {
        $this->client->request(
            'POST',
            '/api/statuses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'code_review', 'title' => 'Code review'])
        );

        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertSame('code_review', $data['name']);
        self::assertSame('Code review', $data['title']);
        self::assertFalse($data['is_system']);
    }

    public function testCreateStatusWithInvalidNameReturnsUnprocessableEntity(): void
    {
        $this->client->request(
            'POST',
            '/api/statuses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Code Review', 'title' => 'Code review'])
        );

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateStatusWithDuplicateNameReturnsConflict(): void
    {
        $this->client->request(
            'POST',
            '/api/statuses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'new', 'title' => 'Duplicate new'])
        );

        self::assertSame(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteCustomUnusedStatusReturnsNoContent(): void
    {
        $this->client->request(
            'POST',
            '/api/statuses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'archived', 'title' => 'Archived'])
        );

        $created = $this->decodeResponse();
        $statusId = (int) $created['id'];

        $this->client->request('DELETE', sprintf('/api/statuses/%d', $statusId));

        self::assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteStatusInUseReturnsConflict(): void
    {
        $this->client->request(
            'POST',
            '/api/statuses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'review', 'title' => 'Review'])
        );

        $created = $this->decodeResponse();
        $statusId = (int) $created['id'];

        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Task in review'])
        );

        $task = $this->decodeResponse();
        $taskId = (int) $task['id'];

        $this->client->request(
            'PATCH',
            sprintf('/api/tasks/%d/status', $taskId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'review'])
        );

        $this->client->request('DELETE', sprintf('/api/statuses/%d', $statusId));

        self::assertSame(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());

        $data = $this->decodeResponse();

        self::assertArrayHasKey('details', $data);
        self::assertSame(1, $data['details']['tasks_count']);
    }

    public function testDeleteSystemStatusReturnsConflict(): void
    {
        $statusId = $this->findStatusIdByName('new');

        $this->client->request('DELETE', sprintf('/api/statuses/%d', $statusId));

        self::assertSame(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteMissingStatusReturnsNotFound(): void
    {
        $this->client->request('DELETE', '/api/statuses/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateStatusWithMalformedJsonReturnsBadRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/statuses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name": "broken"'
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateStatusWithEmptyJsonArrayRootReturnsBadRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/statuses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '[]'
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    private function findStatusIdByName(string $name): int
    {
        $this->client->request('GET', '/api/statuses');

        $data = $this->decodeResponse();

        foreach ($data as $status) {
            if ($status['name'] === $name) {
                return (int) $status['id'];
            }
        }

        self::fail(sprintf('Status "%s" was not found.', $name));
    }
}
