<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetProductQualityScoreController
{
    private GetProductScores $getProductScores;

    public function __construct(GetProductScores $getProductScores)
    {
        $this->getProductScores = $getProductScores;
    }

    public function __invoke(string $productUuid): JsonResponse
    {
        try {
            $productUuid = ProductUuid::fromString($productUuid);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->getProductScores->get($productUuid));
    }
}
