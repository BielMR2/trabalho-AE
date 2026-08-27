<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\EvaluationVote;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<EvaluationVote, EvaluationVote>
 */
final readonly class EvaluationVotePersistProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    /**
     * @param EvaluationVote $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EvaluationVote
    {
        if (!$data instanceof EvaluationVote) {
            throw new NotFoundHttpException();
        }

        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('You must be logged in as a valid user to vote.');
        }

        $existingVote = $this->entityManager->getRepository(EvaluationVote::class)->findOneBy([
            'evaluation' => $data->evaluation,
            'user' => $currentUser
        ]);

        if ($existingVote) {
            $existingVote->value = $data->value;
            $data = $existingVote;
        } else {
            $data->user = $currentUser;
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
