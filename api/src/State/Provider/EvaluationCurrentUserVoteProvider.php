<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Evaluation;
use App\Entity\EvaluationVote;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Decora os providers padrão do Doctrine para Evaluation.
 * Após buscar os dados, popula `currentUserVote` em cada EvaluationRating
 * com o voto do usuário autenticado (null se não votou ou não autenticado).
 *
 * Performance: uma única query busca todos os votos do usuário nos ratings
 * retornados, evitando N+1.
 *
 * @implements ProviderInterface<Evaluation>
 */
final readonly class EvaluationCurrentUserVoteProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $result = $this->collectionProvider->provide($operation, $uriVariables, $context);
        } else {
            $result = $this->itemProvider->provide($operation, $uriVariables, $context);
        }

        if ($result === null) {
            return null;
        }

        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            return $result;
        }

        // Coletar evaluations do resultado (item único ou coleção/paginator)
        $evaluations = [];
        if ($result instanceof Evaluation) {
            $evaluations = [$result];
        } elseif (is_iterable($result)) {
            foreach ($result as $item) {
                if ($item instanceof Evaluation) {
                    $evaluations[] = $item;
                }
            }
        }

        $this->populateCurrentUserVotes($evaluations, $currentUser);

        return $result;
    }

    /**
     * Busca todos os votos do usuário nos ratings das evaluations em uma única query
     * e popula a propriedade currentUserVote de cada rating.
     *
     * @param list<Evaluation> $evaluations
     */
    private function populateCurrentUserVotes(array $evaluations, User $user): void
    {
        // Coletar todos os rating IDs e mapear por ID
        $ratingIds = [];
        $ratingsMap = [];

        foreach ($evaluations as $evaluation) {
            foreach ($evaluation->ratings as $rating) {
                $id = $rating->getId()->toRfc4122();
                $ratingIds[] = $rating->getId();
                $ratingsMap[$id] = $rating;
            }
        }

        if (empty($ratingIds)) {
            return;
        }

        // Uma única query para todos os votos do usuário nos ratings carregados
        /** @var list<array{ratingId: string, value: int}> $votes */
        $votes = $this->entityManager->getRepository(EvaluationVote::class)
            ->createQueryBuilder('v')
            ->select('IDENTITY(v.evaluationRating) as ratingId, v.value')
            ->where('v.user = :user')
            ->andWhere('v.evaluationRating IN (:ratingIds)')
            ->setParameter('user', $user)
            ->setParameter('ratingIds', $ratingIds)
            ->getQuery()
            ->getResult();

        // Mapear votos de volta nos ratings
        foreach ($votes as $vote) {
            $ratingId = $vote['ratingId'];
            if (isset($ratingsMap[$ratingId])) {
                $ratingsMap[$ratingId]->currentUserVote = (int) $vote['value'];
            }
        }
    }
}

