<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Media;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->activateLocaleForChannel('fr_Fr', 'ecommerce');

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_scopable_image']
        ]);

        $this->createProduct('product_one', [
            new SetFamily('a_family'),
            new SetImageValue('a_localizable_scopable_image', 'ecommerce', 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_scopable_image', 'tablet', 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_scopable_image', 'ecommerce', 'fr_FR', $this->getFileInfoKey($this->getFixturePath('ziggy.png'))),
            new SetImageValue('a_localizable_scopable_image', 'tablet', 'fr_FR', $this->getFileInfoKey($this->getFixturePath('ziggy.png'))),
        ]);

        $this->createProduct('product_two', [
            new SetFamily('a_family'),
            new SetImageValue('a_localizable_scopable_image', 'ecommerce', 'en_US', $this->getFileInfoKey($this->getFixturePath('ziggy.png'))),
            new SetImageValue('a_localizable_scopable_image', 'tablet', 'en_US', $this->getFileInfoKey($this->getFixturePath('ziggy.png'))),
            new SetImageValue('a_localizable_scopable_image', 'ecommerce', 'fr_FR', $this->getFileInfoKey($this->getFixturePath('ziggy.png'))),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorStartWith()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::STARTS_WITH, 'aken', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::STARTS_WITH, 'aken', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::CONTAINS, 'ziggy', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::CONTAINS, 'ziggy', ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::CONTAINS, 'igg', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::EQUALS, 'ziggy.png', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::EQUALS, 'ziggy', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::IS_EMPTY, [], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, 'akene', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testErrorLocale()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_image" expects a locale, none given.');

        $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, '2016-09-23']]);
    }

    public function testErrorScope()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_image" expects a scope, none given.');

        $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'fr_FR']]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_image" expects an existing and activated locale, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['locale' => 'NOT_FOUND']]]);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_image" expects an existing scope, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['locale' => 'fr_FR', 'scope' => 'NOT_FOUND']]]);
    }
}
