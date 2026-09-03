<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Establishment;
use App\Entity\Evaluation;
use App\Repository\EstablishmentRepository;
use App\Service\GooglePlacesClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<Evaluation, Evaluation>
 */
final readonly class EvaluationPersistProcessor implements ProcessorInterface
{
  public function __construct(
    #[Autowire(service: PersistProcessor::class)]
    private ProcessorInterface $persistProcessor,
    private EstablishmentRepository $establishmentRepository,
    private GooglePlacesClient $googlePlacesClient,
    private EntityManagerInterface $entityManager
  ) {
  }

  /**
   * @param Evaluation $data
   */
  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Evaluation
  {
    if (!$data instanceof Evaluation) {
      throw new NotFoundHttpException();
    }

    if (!$data->establishmentGooglePlaceId) {
      throw new BadRequestHttpException('establishmentGooglePlaceId is required');
    }

    $establishment = $this->establishmentRepository->findOneBy(['googlePlaceId' => $data->establishmentGooglePlaceId]);
    if (!$establishment) {
      $result = $this->googlePlacesClient->getPlaceDetails($data->establishmentGooglePlaceId);

      $establishment = new Establishment();
      $establishment->googlePlaceId = $data->establishmentGooglePlaceId;
      $establishment->name = $result['displayName']['text'] ?? 'Unknown';
      $establishment->location = sprintf('SRID=4326;POINT(%f %f)', $result['location']['longitude'], $result['location']['latitude']);
      $establishment->address = $result['formattedAddress'] ?? null;
      $establishment->phoneNumber = $result['nationalPhoneNumber'] ?? null;
      $establishment->website = $result['websiteUri'] ?? null;

      $this->entityManager->persist($establishment);
    }

    $data->establishment = $establishment;

    // save entity
    return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
  }
}