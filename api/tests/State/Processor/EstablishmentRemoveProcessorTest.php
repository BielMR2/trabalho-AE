<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Establishment;
use App\Repository\EstablishmentRepository;
use App\State\Processor\EstablishmentRemoveProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class EstablishmentRemoveProcessorTest extends TestCase
{
    private MockObject $removeProcessorMock;
    private MockObject $establishmentRepositoryMock;
    private EstablishmentRemoveProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->establishmentRepositoryMock = $this->createMock(EstablishmentRepository::class);

        $this->processor = new EstablishmentRemoveProcessor(
            $this->removeProcessorMock,
            $this->establishmentRepositoryMock
        );
    }

    #[Test]
    public function itSetsEvaluationsToInactiveAndRemovesEstablishment(): void
    {
        $uuid = Uuid::v4();
        $establishment = $this->createMock(Establishment::class);
        $establishment->method('getId')->willReturn($uuid);
        $operation = $this->createMock(Operation::class);

        $this->establishmentRepositoryMock
            ->expects($this->once())
            ->method('setAllEvaluationsStatus')
            ->with($uuid, false)
        ;

        $this->removeProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($establishment, $operation, [], [])
        ;

        $this->processor->process($establishment, $operation);
    }
}
