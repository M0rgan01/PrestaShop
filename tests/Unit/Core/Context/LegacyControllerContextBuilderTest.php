<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace Core\Context;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Adapter\ContextStateManager;
use PrestaShop\PrestaShop\Adapter\Feature\MultistoreFeature;
use PrestaShop\PrestaShop\Core\Context\Employee;
use PrestaShop\PrestaShop\Core\Context\EmployeeContext;
use PrestaShop\PrestaShop\Core\Context\LegacyController;
use PrestaShop\PrestaShop\Core\Context\LegacyControllerContextBuilder;
use PrestaShop\PrestaShop\Core\Context\ShopContext;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShopBundle\Entity\Repository\TabRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Unit\Core\Configuration\MockConfigurationTrait;

class LegacyControllerContextBuilderTest extends TestCase
{
    use MockConfigurationTrait;

    public function testLegacyBuild(): void
    {
        $contextStateMock = $this->createMock(ContextStateManager::class);

        $contextStateMock->expects($this->once())
            ->method('setController')
            ->with($this->callback(function (LegacyController $legacyController) {
                $this->assertEquals('Cart', $legacyController->className);
                $this->assertEquals('admin', $legacyController->controller_type);
                $this->assertEquals('AdminCartsController', $legacyController->php_self);
                $this->assertEquals('AdminCartsController', $legacyController->controller_name);
                $this->assertEquals(10, $legacyController->id);
                $this->assertEquals(ShopConstraint::ALL_SHOPS, $legacyController->multishop_context);
                $this->assertEquals('carts_controller/', $legacyController->tpl_folder);
                $this->assertEquals('shop', $legacyController->shopLinkType);

                return true;
            }));

        $builder = new LegacyControllerContextBuilder(
            $this->mockEmployeeContext(),
            $this->mockShopContext(),
            $contextStateMock,
            $this->createRequestStack('AdminCartsController'),
            ['AdminCartsController'],
            $this->mockTabRepository(),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(MultistoreFeature::class),
            $this->createMock(ContainerInterface::class),
            'coreDir'
        );

        $builder->buildLegacyContext();
    }

    private function createRequestStack(string $controllerName): RequestStack
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->attributes->set('_legacy_controller', $controllerName);
        $requestStack->push($request);

        return $requestStack;
    }

    private function mockTabRepository(): TabRepository|MockObject
    {
        $repository = $this->createMock(TabRepository::class);
        $repository
            ->method('getIdByClassName')
            ->willReturn(10);

        return $repository;
    }

    private function mockEmployeeContext(): EmployeeContext|MockObject
    {
        $employee = $this->createMock(Employee::class);
        $employee
            ->method('getId')
            ->willReturn(20);

        $employeeContext = $this->createMock(EmployeeContext::class);
        $employeeContext
            ->method('getEmployee')
            ->willReturn($employee);

        return $employeeContext;
    }

    private function mockShopContext(): ShopContext|MockObject
    {
        $shopContext = $this->createMock(ShopContext::class);
        $shopContext
            ->method('getName')
            ->willReturn('TotoShop');

        return $shopContext;
    }
}
