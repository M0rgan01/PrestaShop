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

namespace PrestaShop\PrestaShop\Core\Domain\Product\Pack\ValueObject;

use PrestaShop\PrestaShop\Core\Domain\Product\Pack\Exception\ProductPackConstraintException;

class PackStockType
{
    /**
     * Stock type: only based on pack quantity
     */
    const STOCK_TYPE_PACK_ONLY = 'stock_type_pack_only';

    /**
     * Stock type: only based on products quantity
     */
    const STOCK_TYPE_PRODUCTS_ONLY = 'stock_type_products_only';

    /**
     * Stock type: based on products and pack quantity
     */
    const STOCK_TYPE_BOTH = 'stock_type_both';

    /**
     * Stock type: based on configuration default value
     */
    const STOCK_TYPE_DEFAULT = 'stock_type_default';

    const ALLOWED_PACK_STOCK_TYPES = [
        self::STOCK_TYPE_PACK_ONLY,
        self::STOCK_TYPE_PRODUCTS_ONLY,
        self::STOCK_TYPE_BOTH,
        self::STOCK_TYPE_DEFAULT,
    ];

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     *
     * @throws ProductPackConstraintException
     */
    public function __construct(string $value)
    {
        $this->setStockType($value);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $stockType
     *
     * @throws ProductPackConstraintException
     */
    private function setStockType(string $stockType): void
    {
        if (!in_array($stockType, self::ALLOWED_PACK_STOCK_TYPES)) {
            throw new ProductPackConstraintException(
                sprintf(
                    'Cannot use product pack stock type %s, allowed values are: %s',
                    $stockType,
                    implode(', ', self::ALLOWED_PACK_STOCK_TYPES)
                ),
                ProductPackConstraintException::INVALID_STOCK_TYPE
            );
        }

        $this->value = $stockType;
    }
}
