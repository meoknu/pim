<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAttributeOptionCodes;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform an AttributeInterface entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const ITEM_SEPARATOR = ',';
    const LOCALIZABLE_PATTERN = '{locale}:{value}';
    const GROUP_SEPARATOR = '|';
    const GLOBAL_SCOPE = 'Global';
    const CHANNEL_SCOPE = 'Channel';

    private const MAX_NUMBER_OF_ATTRIBUTE_OPTIONS_CODE = 1000;

    /** @var string[] */
    protected array $supportedFormats = ['flat'];
    protected NormalizerInterface $standardNormalizer;
    protected NormalizerInterface $translationNormalizer;
    protected GetAttributeOptionCodes $getAttributeOptionCodes;

    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $translationNormalizer,
        GetAttributeOptionCodes $getAttributeOptionCodes
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
        $this->getAttributeOptionCodes = $getAttributeOptionCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $standardAttribute = $this->standardNormalizer->normalize($attribute, 'standard', $context);

        $flatAttribute = $standardAttribute;
        $flatAttribute['allowed_extensions'] = implode(self::ITEM_SEPARATOR, $standardAttribute['allowed_extensions']);
        $flatAttribute['available_locales'] = implode(self::ITEM_SEPARATOR, $standardAttribute['available_locales']);
        $flatAttribute['locale_specific'] = $attribute->isLocaleSpecific();

        unset($flatAttribute['labels']);
        /** @phpstan-ignore-line */
        $flatAttribute += $this->normalizeTranslations($standardAttribute['labels'], $context);

        $flatAttribute['options'] = $this->normalizeOptions($attribute);

        $flatAttribute['scope'] = $standardAttribute['scopable'] ? self::CHANNEL_SCOPE : self::GLOBAL_SCOPE;
        $flatAttribute['required'] = (bool) $attribute->isRequired();

        unset($flatAttribute['scopable']);

        return $flatAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    protected function normalizeOptions(AttributeInterface $attribute): ?string
    {
        $attributeOptionCodes = $this->getAttributeOptionCodes->forAttributeCode($attribute->getCode());
        $normalizedOption = null;

        $count = 0;
        foreach ($attributeOptionCodes as $attributeOptionCode) {
            if (null === $normalizedOption) {
                $normalizedOption = 'Code:' . $attributeOptionCode;
            } elseif ($count < self::MAX_NUMBER_OF_ATTRIBUTE_OPTIONS_CODE) {
                $normalizedOption .= self::GROUP_SEPARATOR . 'Code:' . $attributeOptionCode;
            }

            $count++;
        }

        return $normalizedOption;
    }

    private function normalizeTranslations(array $labels, array $context): array
    {
        return $this->translationNormalizer->normalize($labels, 'flat', $context);
    }
}
