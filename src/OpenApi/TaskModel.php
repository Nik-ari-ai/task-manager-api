<?php

namespace App\OpenApi;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[OA\Schema]
class TaskModel
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(type: 'string', example: 'Prepare report')]
    public string $title;

    #[OA\Property(type: 'string', nullable: true, example: 'Sales report for May')]
    public ?string $description;

    #[OA\Property(ref: new Model(type: StatusModel::class))]
    public StatusModel $status;

    #[OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-01-01T12:00:00+00:00')]
    public string $createdAt;

    #[OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-01-01T12:00:00+00:00')]
    public string $updatedAt;
}
