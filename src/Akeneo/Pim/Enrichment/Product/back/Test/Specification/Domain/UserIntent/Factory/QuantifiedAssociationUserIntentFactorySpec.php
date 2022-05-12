<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\QuantifiedAssociationUserIntentFactory;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociationUserIntentFactory::class);
    }

    function it_returns_quantified_association_user_intents()
    {
        $this->create('associations', [
            'QUANTIFIED_ASS' => [
                'products' => [
                    ['identifier' => 'identifier1', 'quantity' => 10],
                    ['identifier' => 'identifier2', 'quantity' => 20]
                ],
                'product_models' => [
                    ['identifier' => 'code1', 'quantity' => 20],
                    ['identifier' => 'code2', 'quantity' => 10]
                ],
            ]
        ])->shouldBeLike([
            new ReplaceAssociatedQuantifiedProducts('QUANTIFIED_ASS', [
                new QuantifiedEntity('identifier1', 10),
                new QuantifiedEntity('identifier2', 20),
            ]),
            new ReplaceAssociatedQuantifiedProductModels('QUANTIFIED_ASS', [
                new QuantifiedEntity('code1', 20),
                new QuantifiedEntity('code2', 10),
            ]),
        ]);
    }
}
