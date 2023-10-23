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

namespace PrestaShop\PrestaShop\Core\Context;

use Doctrine\ORM\NoResultException;
use PrestaShop\PrestaShop\Adapter\ContextStateManager;
use PrestaShop\PrestaShop\Adapter\Feature\MultistoreFeature;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\Util\Inflector;
use PrestaShopBundle\Entity\Repository\TabRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tools;

class LegacyControllerContextBuilder implements LegacyContextBuilderInterface
{
    public function __construct(
        private readonly EmployeeContext $employeeContext,
        private readonly ShopContext $shopContext,
        private readonly ContextStateManager $contextStateManager,
        private readonly RequestStack $requestStack,
        private readonly array $controllersLockedToAllShopContext,
        private readonly TabRepository $tabRepository,
        private readonly TranslatorInterface $translator,
        private readonly MultistoreFeature $multistoreFeature,
        private readonly ContainerInterface $container,
        private readonly string $coreDir,
    ) {
    }

    public function buildLegacyContext(): void
    {
        $request = $this->requestStack->getMainRequest();

        $controllerName = $this->getControllerName($request);
        $multiShopContext = $this->getMultiShopContext($controllerName);
        $id = $this->getTabId($controllerName);
        $token = null;
        if ($this->employeeContext->getEmployee()) {
            $token = Tools::getAdminToken($controllerName . $id . $this->employeeContext->getEmployee()->getId());
        }
        $conf = $this->getConf();
        $error = $this->getError();
        $shopLinkType = $this->getShopLinkType($controllerName);
        $overrideFolder = Tools::toUnderscoreCase(substr($controllerName, 5)) . '/';
        $adminWebPath = $this->getAdminWebPath();
        $controllerType = 'admin';
        $className = $this->getClassName($controllerName);

        $controllerContext = new LegacyController(
            $this->container,
            $controllerName,
            $controllerType,
            $controllerName,
            $multiShopContext,
            $className,
            $id,
            $token,
            $conf,
            $error,
            $shopLinkType,
            $overrideFolder,
            $overrideFolder,
            $adminWebPath
        );

        $this->contextStateManager->setController($controllerContext);
    }

    private function getControllerName(?Request $request): string
    {
        $controllerName = 'AdminController';

        if ($request->attributes->has('_legacy_controller')) {
            $controllerName = $request->attributes->get('_legacy_controller');
        } elseif ($request->query->has('controller')) {
            $controllerName = $request->query->get('controller');
        }

        return $controllerName;
    }

    private function getClassName(string $controllerName): ?string
    {
        switch ($controllerName) {
            case 'AdminAccessController':
                return 'Profile';
            case 'AdminCarrierWizardController':
                return 'Carrier';
            case 'AdminImagesController':
                return 'ImageType';
            case 'AdminReturnController':
                return 'OrderReturn';
            case 'AdminSearchConfController':
                return 'Alias';
            case 'AdminConfigureFaviconBoController':
                return 'Configuration';
            default:
                if (preg_match('/Admin([a-zA-Z]+)Controller/', $controllerName, $matches)) {
                    return Inflector::getInflector()->singularize($matches[1]);
                } else {
                    return null;
                }
        }
    }

    private function getShopLinkType(string $controllerName): ?string
    {
        if ($controllerName === 'AdminCartsController' || $controllerName === 'AdminCustomerThreadsController') {
            return 'shop';
        } else {
            return !$this->multistoreFeature->isActive() ? '' : null;
        }
    }

    private function getTabId(string $controllerName): int
    {
        try {
            return $this->tabRepository->getIdByClassName($controllerName);
        } catch (NoResultException) {
            return -1;
        }
    }

    private function getMultiShopContext(string $controllerName): int
    {
        if (in_array($controllerName, $this->controllersLockedToAllShopContext)) {
            return ShopConstraint::ALL_SHOPS;
        } else {
            return ShopConstraint::ALL_SHOPS | ShopConstraint::SHOP_GROUP | ShopConstraint::SHOP;
        }
    }

    private function getConf(): array
    {
        return [
            1 => $this->translator->trans('Successful deletion', [], 'Admin.Notifications.Success'),
            2 => $this->translator->trans('The selection has been successfully deleted.', [], 'Admin.Notifications.Success'),
            3 => $this->translator->trans('Successful creation', [], 'Admin.Notifications.Success'),
            4 => $this->translator->trans('Successful update', [], 'Admin.Notifications.Success'),
            5 => $this->translator->trans('The status has been successfully updated.', [], 'Admin.Notifications.Success'),
            6 => $this->translator->trans('The settings have been successfully updated.', [], 'Admin.Notifications.Success'),
            7 => $this->translator->trans('Image successfully deleted.', [], 'Admin.Notifications.Success'),
            8 => $this->translator->trans('The module was successfully downloaded.', [], 'Admin.Modules.Notification'),
            9 => $this->translator->trans('The thumbnails were successfully regenerated.', [], 'Admin.Notifications.Success'),
            10 => $this->translator->trans('The message was successfully sent to the customer.', [], 'Admin.Orderscustomers.Notification'),
            11 => $this->translator->trans('Comment successfully added.', [], 'Admin.Notifications.Success'),
            12 => $this->translator->trans('Module(s) installed successfully.', [], 'Admin.Modules.Notification'),
            13 => $this->translator->trans('Module(s) uninstalled successfully.', [], 'Admin.Modules.Notification'),
            14 => $this->translator->trans('The translation was successfully copied.', [], 'Admin.International.Notification'),
            15 => $this->translator->trans('The translations have been successfully added.', [], 'Admin.International.Notification'),
            16 => $this->translator->trans('The module transplanted successfully to the hook.', [], 'Admin.Modules.Notification'),
            17 => $this->translator->trans('The module was successfully removed from the hook.', [], 'Admin.Modules.Notification'),
            18 => $this->translator->trans('Successful upload.', [], 'Admin.Notifications.Success'),
            19 => $this->translator->trans('Duplication was completed successfully.', [], 'Admin.Notifications.Success'),
            20 => $this->translator->trans('The translation was added successfully, but the language has not been created.', [], 'Admin.International.Notification'),
            21 => $this->translator->trans('Module reset successfully.', [], 'Admin.Modules.Notification'),
            22 => $this->translator->trans('Module deleted successfully.', [], 'Admin.Modules.Notification'),
            23 => $this->translator->trans('Localization pack imported successfully.', [], 'Admin.International.Notification'),
            24 => $this->translator->trans('Localization pack imported successfully.', [], 'Admin.International.Notification'),
            25 => $this->translator->trans('The selected images have successfully been moved.', [], 'Admin.Notifications.Success'),
            26 => $this->translator->trans('Your cover image selection has been saved.', [], 'Admin.Notifications.Success'),
            27 => $this->translator->trans('The image\'s shop association has been modified.', [], 'Admin.Notifications.Success'),
            28 => $this->translator->trans('A zone has been assigned to the selection successfully.', [], 'Admin.Notifications.Success'),
            29 => $this->translator->trans('Successful upgrade.', [], 'Admin.Notifications.Success'),
            30 => $this->translator->trans('A partial refund was successfully created.', [], 'Admin.Orderscustomers.Notification'),
            31 => $this->translator->trans('The discount was successfully generated.', [], 'Admin.Catalog.Notification'),
            32 => $this->translator->trans('Successfully signed in to PrestaShop Addons.', [], 'Admin.Modules.Notification'),
        ];
    }

    private function getError(): array
    {
        return [
            1 => $this->translator->trans(
                'The root category of the shop %shop% is not associated with the current shop. You can\'t access this page. Please change the root category of the shop.',
                [
                    '%shop%' => $this->shopContext->getName(),
                ],
                'Admin.Catalog.Notification'
            ),
        ];
    }

    private function getAdminWebPath(): ?string
    {
        $adminWebPath = null;

        if (defined('_PS_ADMIN_DIR_')) {
            $adminWebPath = str_ireplace($this->coreDir, '', _PS_ADMIN_DIR_);
            $adminWebPath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $adminWebPath);
        }

        return $adminWebPath;
    }
}
