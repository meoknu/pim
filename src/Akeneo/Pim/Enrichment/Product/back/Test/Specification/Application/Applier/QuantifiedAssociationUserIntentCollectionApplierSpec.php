<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\Application\Applier\QuantifiedAssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QuantifiedAssociationUserIntentCollectionApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater,
        GetViewableProducts $getViewableProducts,
        GetViewableProductModels $getViewableProductModels
    ) {
        $this->beConstructedWith($productUpdater, $getViewableProducts, $getViewableProductModels);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociationUserIntentCollectionApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_associates_quantified_products_by_updating_a_quantity(
        ObjectUpdaterInterface $productUpdater,
    ) {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('foo', 8)]),
            ]),
            $product,
            10
        );
    }

    function it_associates_quantified_products_by_adding_one_association(
        ObjectUpdaterInterface $productUpdater,
    ) {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'bar', 'quantity' => 4],
                    ['identifier' => 'baz', 'quantity' => 3],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('foo', 8), new QuantifiedEntity('baz', 3)]),
            ]),
            $product,
            10
        );
    }

    function it_does_nothing_when_product_is_already_associated_with_the_same_quantity(
        ObjectUpdaterInterface $productUpdater,
    ) {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('bar', 4)]),
            ]),
            $product,
            10
        );
    }

    function it_dissociates_quantified_products(ObjectUpdaterInterface $productUpdater)
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new DissociateQuantifiedProducts('bundle', ['foo', 'baz']),
            ]),
            $product,
            10
        );
    }

    function it_dissociates_all_quantified_products(ObjectUpdaterInterface $productUpdater)
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new DissociateQuantifiedProducts('bundle', ['foo', 'bar']),
            ]),
            $product,
            10
        );
    }

    function it_replaces_quantified_products(ObjectUpdaterInterface $productUpdater, GetViewableProducts $getViewableProducts)
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                    ['identifier' => 'baz', 'quantity' => 5],
                ],
            ],
        ]));

        $getViewableProducts->fromProductIdentifiers(['bar', 'baz', 'foo'], 10)->willReturn(['bar', 'foo']);

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'baz', 'quantity' => 5],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new ReplaceAssociatedQuantifiedProducts('bundle', [
                    new QuantifiedEntity('foo', 8),
                ]),
            ]),
            $product,
            10
        );
    }

    function it_associates_quantified_product_models_by_updating_a_quantity(
        ObjectUpdaterInterface $productUpdater,
    ) {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'product_models' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'product_models' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProductModels('bundle', [new QuantifiedEntity('foo', 8)]),
            ]),
            $product,
            10
        );
    }
}
