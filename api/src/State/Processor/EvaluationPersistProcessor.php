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
use App\Service\GeoIpService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
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
    private EntityManagerInterface $entityManager,
    private RequestStack $requestStack,
    private GeoIpService $geoIpService,
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

      if (isset($result['addressComponents'])) {
          foreach ($result['addressComponents'] as $component) {
              if (in_array('country', $component['types'] ?? [], true)) {
                  $establishment->countryCode = $component['shortText'] ?? null;
                  break;
              }
          }
      }

      $this->entityManager->persist($establishment);
    }

    $clientIp = $this->requestStack->getCurrentRequest()?->getClientIp();
    $establishmentCountry = $establishment->countryCode;

    if ($clientIp && $establishmentCountry) {
        $userCountry = $this->geoIpService->getCountryCode($clientIp);
        
        if ($userCountry && $userCountry !== $establishmentCountry) {
            throw new BadRequestHttpException('User must be in the same country as the establishment to submit an evaluation');
        }
    }

    $data->establishment = $establishment;
    $data->countryCode = $establishment->countryCode;

    // save entity
    return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
  }
}