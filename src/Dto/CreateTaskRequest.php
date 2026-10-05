<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTaskRequest
{
    #[Assert\NotBlank(message: 'The title must not be blank.')]
    #[Assert\Type(type: 'string', message: 'The title must be a string.')]
    #[Assert\Length(max: 255, maxMessage: 'The title must not exceed {{ limit }} characters.')]
    public mixed $title = null;

    #[Assert\Type(type: 'string', message: 'The description must be a string.')]
    #[Assert\Length(max: 65535, maxMessage: 'The description is too long.')]
    public mixed $description = null;
}
