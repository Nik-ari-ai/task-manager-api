<?php

namespace App\Request;

use App\Exception\InvalidJsonException;
use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JsonRequestDecoder
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Decodes the JSON body of the request, maps it onto a fresh instance of the
     * given DTO class and validates it.
     *
     * @template T of object
     *
     * @param class-string<T> $dtoClass
     *
     * @return T
     */
    public function decodeAndValidate(Request $request, string $dtoClass): object
    {
        $data = $this->decodeBody($request);

        $dto = $this->mapToDto($data, $dtoClass);

        $this->validate($dto);

        return $dto;
    }

    private function decodeBody(Request $request): \stdClass
    {
        $content = $request->getContent();

        if ('' === trim($content)) {
            throw new InvalidJsonException('The request body must not be empty.');
        }

        try {
            $decoded = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidJsonException('The request body contains malformed JSON.');
        }

        if (!$decoded instanceof \stdClass) {
            throw new InvalidJsonException('The request body must be a JSON object.');
        }

        return $decoded;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $dtoClass
     *
     * @return T
     */
    private function mapToDto(\stdClass $data, string $dtoClass): object
    {
        $dto = new $dtoClass();

        foreach (get_object_vars($data) as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->{$key} = $value;
            }
        }

        return $dto;
    }

    private function validate(object $dto): void
    {
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}
