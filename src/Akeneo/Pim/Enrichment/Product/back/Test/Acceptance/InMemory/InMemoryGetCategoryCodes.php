<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetCategoryCodes implements GetCategoryCodes
{
    public function __construct(private ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        $productIdentifiers = \array_map(
            static fn (ProductIdentifier $identifier): string => \strtolower($identifier->asString()),
            $productIdentifiers
        );

        $results = [];
        /** @var ProductInterface $product */
        foreach ($this->productRepository->findAll() as $product) {
            if (\in_array(\strtolower($product->getIdentifier()), $productIdentifiers)) {
                $results[$product->getIdentifier()] = $product->getCategoryCodes();
            }
        }

        return $results;
    }
}
