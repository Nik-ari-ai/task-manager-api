<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema]
class StatusModel
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(type: 'string', example: 'new')]
    public string $name;

    #[OA\Property(type: 'string', example: 'New')]
    public string $title;

    #[OA\Property(property: 'is_system', type: 'boolean', example: true)]
    public bool $isSystem;
}
