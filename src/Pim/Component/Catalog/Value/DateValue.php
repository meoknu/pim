<?php

namespace Pim\Component\Catalog\Value;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AbstractValue;

/**
 * Product value for "pim_catalog_date" attribute types
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateValue extends AbstractValue implements DateValueInterface
{
    /** @var \DateTime */
    protected $data;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param \DateTime|null     $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, \DateTime $data = null)
    {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return null !== $this->data ? $this->data->format('Y-m-d') : '';
    }
}
