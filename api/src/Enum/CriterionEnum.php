<?php

declare(strict_types=1);

namespace App\Enum;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

/**
 * Critérios de acessibilidade para avaliação de um estabelecimento.
 */
#[ApiResource(
  types: ['http://schema.org/Enumeration'],
  operations: [
    new GetCollection(provider: [self::class, 'getCases']),
    new Get(provider: [self::class, 'getCase']),
  ]
)]
enum CriterionEnum: string
{
  use EnumApiResourceTrait;

  /** Acesso físico adequado para pessoas em cadeira de rodas (rampas, elevadores, portas largas). */
  case WheelchairAccessible = 'wheelchair_accessible';

  /** Banheiros adaptados e acessíveis. */
  case AccessibleRestroom = 'accessible_restroom';

  /** Piso tátil para orientação de pessoas com deficiência visual. */
  case TactilePaving = 'tactile_paving';

  /** Sinalização em Braille ou em alto-relevo. */
  case BrailleSignage = 'braille_signage';

  /** Atendimento disponível em Língua Brasileira de Sinais (Libras). */
  case SignLanguage = 'sign_language';

  /** Permitida e facilitada a entrada de animais de serviço (ex: cão-guia). */
  case ServiceAnimalAllowed = 'service_animal_allowed';
}