<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelLocaleRateCollectionSpec extends ObjectBehavior
{
    public function it_can_be_constructed_from_an_array_of_rates_as_integer()
    {
        $this->beConstructedThrough('fromArrayInt', [[
            'mobile' => [
                'en_US' => 87,
                'fr_FR' => 34,
            ],
            'print' => [
                'en_US' => 42,
            ],
        ]]);

        $expectedRates = [
            'mobile' => [
                'en_US' => new Rate(87),
                'fr_FR' => new Rate(34),
            ],
            'print' => [
                'en_US' => new Rate(42),
            ],
        ];

        $rates = \iterator_to_array($this->getWrappedObject());
        Assert::eq($expectedRates, $rates);
    }

    public function it_can_be_constructed_from_an_array_of_normalized_rates(): void
    {
        $this->beConstructedThrough('fromNormalizedRates', [[
            'mobile' => [
                'en_US' => [
                    'rank' => 5,
                    'value' => 42,
                ],
                'fr_FR' => [
                    'rank' => 2,
                    'value' => 86,
                ],
            ],
            'print' => [
                'en_US' => [
                    'rank' => 3,
                    'value' => 73,
                ],
            ],
        ]]);

        $expectedRates = [
            'mobile' => [
                'en_US' => new Rate(42),
                'fr_FR' => new Rate(86),
            ],
            'print' => [
                'en_US' => new Rate(73),
            ],
        ];

        $rates = \iterator_to_array($this->getWrappedObject());
        Assert::eq($expectedRates, $rates);
    }

    public function it_throws_an_exception_if_it_is_constructed_from_malformed_normalized_rates(): void
    {
        $this->beConstructedThrough('fromNormalizedRates', [[
            'mobile' => [
                'en_US' => [
                    'rank' => 5,
                    'value' => 42,
                ],
                'fr_FR' => [
                    'rank' => 2,
                ],
            ],
        ]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_rate_for_a_channel_and_locale()
    {
        $rateMobileEn = new Rate(42);
        $rateMobileFr = new Rate(56);
        $ratePrintEn = new Rate(73);

        $this->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), $rateMobileEn);
        $this->addRate(new ChannelCode('mobile'), new LocaleCode('fr_FR'), $rateMobileFr);
        $this->addRate(new ChannelCode('print'), new LocaleCode('en_US'), $ratePrintEn);

        $this->getByChannelAndLocale(new ChannelCode('mobile'), new LocaleCode('fr_FR'))->shouldReturn($rateMobileFr);
    }

    public function it_returns_the_rates_in_a_normalized_format(): void
    {
        $rateMobileEn = new Rate(42);
        $rateMobileFr = new Rate(86);
        $ratePrintEn = new Rate(73);

        $this->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), $rateMobileEn);
        $this->addRate(new ChannelCode('mobile'), new LocaleCode('fr_FR'), $rateMobileFr);
        $this->addRate(new ChannelCode('print'), new LocaleCode('en_US'), $ratePrintEn);

        $this->toNormalizedRates()->shouldReturn([
            'mobile' => [
                'en_US' => [
                    'rank' => 5,
                    'value' => 42,
                ],
                'fr_FR' => [
                    'rank' => 2,
                    'value' => 86,
                ],
            ],
            'print' => [
                'en_US' => [
                    'rank' => 3,
                    'value' => 73,
                ],
            ],
        ]);
    }
}
