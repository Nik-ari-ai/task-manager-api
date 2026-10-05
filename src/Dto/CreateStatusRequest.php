<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateStatusRequest
{
    #[Assert\NotBlank(message: 'The name must not be blank.')]
    #[Assert\Type(type: 'string', message: 'The name must be a string.')]
    #[Assert\Length(max: 255, maxMessage: 'The name must not exceed {{ limit }} characters.')]
    #[Assert\Regex(
        pattern: '/^[a-z][a-z0-9_]*$/',
        message: 'The name must start with a lowercase letter and contain only lowercase letters, digits and underscores.'
    )]
    public mixed $name = null;

    #[Assert\NotBlank(message: 'The title must not be blank.')]
    #[Assert\Type(type: 'string', message: 'The title must be a string.')]
    #[Assert\Length(max: 255, maxMessage: 'The title must not exceed {{ limit }} characters.')]
    public mixed $title = null;
}
