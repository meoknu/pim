<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MetricToStringDataConverter implements ValueDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttributes(AttributeInterface $sourceAttribute, AttributeInterface $targetAttribute): bool
    {
        return AttributeTypes::METRIC === $sourceAttribute->getType() &&
            in_array($targetAttribute->getType(), [AttributeTypes::TEXT, AttributeTypes::TEXTAREA]);
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ValueInterface $sourceValue, AttributeInterface $targetAttribute)
    {
        $metric = $sourceValue->getData();
        Assert::isInstanceOf($metric, MetricInterface::class);

        // Do not "%F" to render the float because it adds some unexpected 0. "%s" fixes that
        return \sprintf('%s %s', (float) $metric->getData(), $metric->getUnit());
    }
}
