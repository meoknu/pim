<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingFromProductIdentifiersQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUuidMappingFromProductIdentifiersQuery implements GetUuidMappingFromProductIdentifiersQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(array $productIdentifiers): UuidMapping
    {
        if (empty($productIdentifiers)) {
            return UuidMapping::createFromMapping([]);
        }

        $query = <<<SQL
            SELECT BIN_TO_UUID(uuid) as uuid, identifier
            FROM pim_catalog_product
            WHERE identifier IN (:product_identifiers)
            AND uuid IS NOT NULL
        SQL;

        $mapping = array_column($this->connection->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative(), 'identifier', 'uuid');

        return UuidMapping::createFromMapping($mapping);
    }
}
