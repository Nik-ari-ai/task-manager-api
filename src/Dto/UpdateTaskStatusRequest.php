<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTaskStatusRequest
{
    #[Assert\NotBlank(message: 'The status must not be blank.')]
    #[Assert\Type(type: 'string', message: 'The status must be a string.')]
    #[Assert\Length(max: 255, maxMessage: 'The status must not exceed {{ limit }} characters.')]
    public mixed $status = null;
}
