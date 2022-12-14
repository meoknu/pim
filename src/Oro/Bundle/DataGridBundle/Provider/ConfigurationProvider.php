<?php

namespace Oro\Bundle\DataGridBundle\Provider;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class ConfigurationProvider implements ConfigurationProviderInterface
{
    protected array $rawConfiguration;
    protected SystemAwareResolver $resolver;
    protected array $processedConfiguration = [];

    public function __construct(array $rawConfiguration, SystemAwareResolver $resolver)
    {
        $this->rawConfiguration = $rawConfiguration;
        $this->resolver = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable($gridName)
    {
        return isset($this->rawConfiguration[$gridName]);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration($gridName)
    {
        if (!isset($this->rawConfiguration[$gridName])) {
            throw new \RuntimeException(sprintf('A configuration for "%s" datagrid was not found.', $gridName));
        }

        if (!isset($this->processedConfiguration[$gridName])) {
            $config = $this->resolver->resolve($gridName, $this->rawConfiguration[$gridName]);
            $this->processedConfiguration[$gridName] = $config;
        }

        return DatagridConfiguration::createNamed($gridName, $this->processedConfiguration[$gridName]);
    }
}
