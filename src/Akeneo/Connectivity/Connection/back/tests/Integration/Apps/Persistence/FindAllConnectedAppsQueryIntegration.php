<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindAllConnectedAppsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAllConnectedAppsQueryIntegration extends TestCase
{
    private FindAllConnectedAppsQuery $query;
    private ConnectionLoader $connectionLoader;
    private UserGroupLoader $userGroupLoader;
    private Connection $connection;
    private ConnectedAppLoader $connectedAppLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(FindAllConnectedAppsQuery::class);
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_finds_all_ordered_by_name()
    {
        // Test App
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'connected_test_app',
            ['read_products'],
            true,
        );
        $expectedTestApp = [
            'id' => '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'name' => 'connected_test_app',
            'connection_code' => 'connected_test_app',
            'author' => 'Akeneo',
            'logo' => null,
            'user_group_name' => 'app_connected_test_app',
            'categories' => [],
            'partner' => null,
            'certified' => false,
            'is_test_app' => true,
            'is_pending' => false,
            'scopes' => ['read_products'],
        ];

        // App
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'connected_app',
        );
        $expectedApp = [
            'id' => '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'name' => 'connected_app',
            'connection_code' => 'connected_app',
            'user_group_name' => 'app_connected_app',
            'logo' => 'http://example.com/logo.png',
            'author' => 'Akeneo',
            'categories' => ['ecommerce'],
            'is_test_app' => false,
            'scopes' => ['read_products'],
            'partner' => null,
            'certified' => false,
            'is_pending' => false,
        ];

        // Pending App
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'cc345scc-f852-4956-bf9b-1a1ec1b0d145',
            'pending_app',
            ['read_products'],
            false,
            true,
        );
        $expectedPendingApp = [
            'id' => 'cc345scc-f852-4956-bf9b-1a1ec1b0d145',
            'name' => 'pending_app',
            'scopes' => ['read_products'],
            'connection_code' => 'pending_app',
            'logo' => 'http://example.com/logo.png',
            'author' => 'Akeneo',
            'user_group_name' => 'app_pending_app',
            'categories' => ['ecommerce'],
            'certified' => false,
            'partner' => null,
            'is_test_app' => false,
            'is_pending' => true,
        ];

        $connectedApps = $this->query->execute();

        Assert::assertEquals($expectedApp, $connectedApps[0]->normalize());
        Assert::assertEquals($expectedTestApp, $connectedApps[1]->normalize());
        Assert::assertEquals($expectedPendingApp, $connectedApps[2]->normalize());
    }
}
