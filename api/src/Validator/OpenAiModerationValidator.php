<?php

declare(strict_types=1);

namespace App\Validator;

use OpenAI\Client;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class OpenAiModerationValidator extends ConstraintValidator
{
    public function __construct(
        private Client $openAi
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof OpenAiModeration) {
            throw new UnexpectedTypeException($constraint, OpenAiModeration::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $response = $this->openAi->moderations()->create([
            'input' => $value,
        ]);

        if ($response->results[0]->flagged) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
