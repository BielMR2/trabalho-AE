<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class OpenAiModeration extends Constraint
{
    public string $message = 'O comentário viola nossas diretrizes de comunidade por conter linguagem inapropriada, assédio ou discurso de ódio.';
}
