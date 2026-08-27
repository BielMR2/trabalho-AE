<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Establishment;
use App\Repository\EstablishmentRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Establishment, void>
 */
final readonly class EstablishmentRemoveProcessor implements ProcessorInterface
{
  /**
   * @param RemoveProcessor $removeProcessor
   */
  public function __construct(
    #[Autowire(service: RemoveProcessor::class)]
    private ProcessorInterface $removeProcessor,
    #[Autowire(service: EstablishmentRepository::class)]
    private EstablishmentRepository $establishmentRepository,
  ) {
  }

  /**
   * @param Establishment $data
   */
  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
  {
    $this->establishmentRepository->setAllEvaluationsStatus($data->getId(), false);

    // remove entity
    $this->removeProcessor->process($data, $operation, $uriVariables, $context);
  }
}
