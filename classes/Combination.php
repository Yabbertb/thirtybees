<?php
/**
 * 2007-2016 PrestaShop
 *
 * thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2024 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.thirtybees.com for more information.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2024 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

use CoreUpdater\TableSchema;

/**
 * Class CombinationCore
 */
class CombinationCore extends ObjectModel
{
    /**
     * @var array Object model definition
     */
    public static $definition = [
        'table'   => 'product_attribute',
        'primary' => 'id_product_attribute',
        'fields'  => [
            'id_product'         => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId', 'required' => true],
            'reference'          => ['type' => self::TYPE_STRING, 'size' => 32],
            'supplier_reference' => ['type' => self::TYPE_STRING, 'size' => 32],
            'location'           => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 64],
            'ean13'              => ['type' => self::TYPE_STRING, 'validate' => 'isEan13', 'size' => 13],
            'upc'                => ['type' => self::TYPE_STRING, 'validate' => 'isUpc', 'size' => 12],
            'wholesale_price'    => ['type' => self::TYPE_PRICE, 'shop' => true, 'validate' => 'isPrice', 'size' => 20, 'dbDefault' => '0.000000'],
            'price'              => ['type' => self::TYPE_PRICE, 'shop' => true, 'validate' => 'isNegativePrice', 'size' => 20, 'dbDefault' => '0.000000'],
            'ecotax'             => ['type' => self::TYPE_PRICE, 'shop' => true, 'validate' => 'isPrice', 'dbDefault' => '0.000000'],
            'quantity'           => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'size' => 10, 'signed' => true, 'dbDefault' => '0'],
            'weight'             => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isFloat', 'dbDefault' => '0.000000'],
            'width'              => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isUnsignedFloat', 'dbDefault' => '0.000000'],
            'height'             => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isUnsignedFloat', 'dbDefault' => '0.000000'],
            'depth'              => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isUnsignedFloat', 'dbDefault' => '0.000000'],
            'unit_price_impact'  => ['type' => self::TYPE_PRICE, 'shop' => true, 'validate' => 'isNegativePrice', 'size' => 20, 'dbDefault' => '0.000000'],
            'default_on'         => ['type' => self::TYPE_BOOL, 'allow_null' => true, 'shop' => true, 'validate' => 'isBool'],
            'minimal_quantity'   => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId', 'required' => true, 'dbDefault' => '1'],
            'available_date'     => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat', 'dbType' => 'date', 'dbDefault' => '1970-01-01'],
        ],
        'keys' => [
            'product_attribute' => [
                'product_default'                 => ['type' => ObjectModel::UNIQUE_KEY, 'columns' => ['id_product', 'default_on']],
                'id_product_id_product_attribute' => ['type' => ObjectModel::KEY, 'columns' => ['id_product_attribute', 'id_product']],
                'product_attribute_product'       => ['type' => ObjectModel::KEY, 'columns' => ['id_product']],
                'reference'                       => ['type' => ObjectModel::KEY, 'columns' => ['reference']],
                'supplier_reference'              => ['type' => ObjectModel::KEY, 'columns' => ['supplier_reference']],
            ],
            'product_attribute_shop' => [
                'id_product' => ['type' => ObjectModel::UNIQUE_KEY, 'columns' => ['id_product', 'id_shop', 'default_on']],
            ],
        ],
    ];

    /**
     * @var int $id_product
     */
    public $id_product;

    /**
     * @var string $location
     */
    public $location;

    /**
     * @var string $ean13
     */
    public $ean13;

    /**
     * @var string $upc
     */
    public $upc;

    /**
     * @var int $quantity
     */
    public $quantity;

    /**
     * @var string $reference
     */
    public $reference;

    /**
     * @var string $supplier_reference
     */
    public $supplier_reference;

    /**
     * @var float $wholesale_price
     */
    public $wholesale_price;

    /**
     * @var float $price
     */
    public $price;

    /**
     * @var float $ecotax
     */
    public $ecotax;

    /**
     * @var float $weight
     */
    public $weight;

    /**
     * @var float $width Impact on width dimension
     */
    public $width;

    /**
     * @var float $height Impact on height dimension
     */
    public $height;

    /**
     * @var float height Impact on depth dimension
     */
    public $depth;

    /**
     * @var float $unit_price_impact
     */
    public $unit_price_impact;

    /**
     * @var int $minimal_quantity
     */
    public $minimal_quantity = 1;

    /**
     * @var bool $default_on
     */
    public $default_on;

    /**
     * @var string $available_date
     */
    public $available_date = '0000-00-00';

    /**
     * @var array Webservice parameters
     */
    protected $webserviceParameters = [
        'objectNodeName'  => 'combination',
        'objectsNodeName' => 'combinations',
        'fields'          => [
            'id_product' => [
                'required' => true,
                'xlink_resource' => 'products'
            ],
        ],
        'associations'    => [
            'product_option_values' => [
                'resource' => 'product_option_value',
                'fields'   => ['id' => []],
            ],
            'images'                => [
                'resource' => 'image',
                'api' => 'images/products',
                'fields'   => ['id' => []],
            ],
        ],
    ];

    /**
     * @var array<int, int>]|null
     */
    protected $attributes = null;

    /**
     * This method is allowed to know if a feature is active
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    public static function isFeatureActive()
    {
        static $featureActive = null;

        if ($featureActive === null) {
            $featureActive = Configuration::get('PS_COMBINATION_FEATURE_ACTIVE');
        }

        return $featureActive;
    }

    /**
     * This method is allowed to know if a Combination entity is currently used
     *
     * @param string|null $table
     * @param bool $hasActiveColumn
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    public static function isCurrentlyUsed($table = null, $hasActiveColumn = false)
    {
        return parent::isCurrentlyUsed('product_attribute');
    }

    /**
     * For a given product_attribute reference, returns the corresponding id
     *
     * @param int $idProduct
     * @param string $reference
     *
     * @return int id
     *
     * @throws PrestaShopException
     */
    public static function getIdByReference($idProduct, $reference)
    {
        if (empty($reference)) {
            return 0;
        }

        return Db::readOnly()->getValue(
            (new DbQuery())
                ->select('pa.id_product_attribute')
                ->from('product_attribute', 'pa')
                ->where('pa.reference LIKE \'%'.pSQL($reference).'%\'')
                ->where('pa.id_product = '.(int) $idProduct)
        );
    }

    /**
     * Retrive the price of combination
     *
     * @param int $idProductAttribute
     *
     * @return float mixed
     *
     * @throws PrestaShopException
     */
    public static function getPrice($idProductAttribute)
    {
        return (float)Db::readOnly()->getValue(
            (new DbQuery())
                ->select('product_attribute_shop.`price`')
                ->from('product_attribute', 'pa')
                ->join(Shop::addSqlAssociation('product_attribute', 'pa'))
                ->where('pa.`id_product_attribute` = '.(int) $idProductAttribute)
        );
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function delete()
    {
        if (!parent::delete()) {
            return false;
        }

        // Removes the product from StockAvailable, for the current shop
        StockAvailable::removeProductFromStockAvailable((int) $this->id_product, (int) $this->id);

        if ($specificPrices = SpecificPrice::getByProductId((int) $this->id_product, (int) $this->id)) {
            foreach ($specificPrices as $specificPrice) {
                $price = new SpecificPrice((int) $specificPrice['id_specific_price']);
                $price->delete();
            }
        }

        if (!$this->hasMultishopEntries() && !$this->deleteAssociations()) {
            return false;
        }

        $this->deleteFromSupplier($this->id_product);
        Product::updateDefaultAttribute($this->id_product);
        Tools::clearColorListCache((int) $this->id_product);

        return true;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function deleteAssociations()
    {
        $conn = Db::getInstance();
        $result = $conn->delete('product_attribute_combination', '`id_product_attribute` = '.(int) $this->id);
        $result = $conn->delete('cart_product', '`id_product_attribute` = '.(int) $this->id) && $result;
        $result = $conn->delete('product_attribute_image', '`id_product_attribute` = '.(int) $this->id) && $result;
        return $result;
    }

    /**
     * @param int $idProduct
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function deleteFromSupplier($idProduct)
    {
        return Db::getInstance()->delete('product_supplier', 'id_product = '.(int) $idProduct.' AND id_product_attribute = '.(int) $this->id);
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($autoDate = true, $nullValues = false)
    {
        if ($this->default_on) {
            $this->default_on = 1;
        } else {
            $this->default_on = null;
        }

        if (!parent::add($autoDate, $nullValues)) {
            return false;
        }

        $product = new Product((int) $this->id_product);
        if ($product->getType() == Product::PTYPE_VIRTUAL) {
            StockAvailable::setProductOutOfStock((int) $this->id_product, StockAvailable::OUT_OF_STOCK_ALLOW, null, (int) $this->id);
        } else {
            StockAvailable::setProductOutOfStock((int) $this->id_product, StockAvailable::outOfStock((int) $this->id_product), null, $this->id);
        }

        SpecificPriceRule::applyAllRules([(int) $this->id_product]);

        Product::updateDefaultAttribute($this->id_product);

        return true;
    }

    /**
     * @param bool $nullValues
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function update($nullValues = false)
    {
        if ($this->default_on) {
            $this->default_on = 1;
        } else {
            $this->default_on = null;
        }

        $return = parent::update($nullValues);
        Product::updateDefaultAttribute($this->id_product);

        return $return;
    }

    /**
     * @param array $values
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function setWsProductOptionValues($values)
    {
        $idsAttributes = [];
        foreach ($values as $value) {
            if (isset($value['id']) && (int)$value['id']) {
                $idsAttributes[] = (int)$value['id'];
            }
        }

        return $this->setAttributes($idsAttributes);
    }

    /**
     * @param int[] $idsAttribute
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function setAttributes($idsAttribute)
    {
        $conn = Db::getInstance();
        $result = $conn->delete('product_attribute_combination', '`id_product_attribute` = '.(int) $this->id);

        if ($result && is_array($idsAttribute) && $idsAttribute) {
            $sqlValues = [];
            foreach ($idsAttribute as $value) {
                $value = (int)$value;
                if ($value) {
                    $sqlValues[] = [
                        'id_attribute' => $value,
                        'id_product_attribute' => (int)$this->id,
                    ];
                }
            }
            if ($sqlValues) {
                $result = $conn->insert('product_attribute_combination', $sqlValues);
            }
        }

        return $result;
    }

    /**
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getWsProductOptionValues()
    {
        $result = Db::readOnly()->getArray(
            (new DbQuery())
                ->select('a.`id_attribute` AS `id`')
                ->from('product_attribute_combination', 'a')
                ->join(Shop::addSqlAssociation('attribute', 'a'))
                ->where('a.`id_product_attribute` = '.(int) $this->id)
        );

        return $result;
    }

    /**
     * @return array
     *
     * @throws PrestaShopException
     */
    public function getWsImages()
    {
        $result = Db::readOnly()->getArray(
            (new DbQuery())
                ->select('a.`id_image` AS `id`')
                ->from('product_attribute_image', 'a')
                ->join(Shop::addSqlAssociation('product_attribute', 'a'))
                ->where('a.`id_product_attribute` = '.(int) $this->id)
        );

        return $result;
    }

    /**
     * @param array $values
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    public function setWsImages($values)
    {
        $idsImages = [];
        foreach ($values as $value) {
            if (isset($value['id']) && (int)$value['id']) {
                $idsImages[] = (int) $value['id'];
            }
        }

        return $this->setImages($idsImages);
    }

    /**
     * @param array $idsImage
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function setImages($idsImage)
    {
        $conn = Db::getInstance();
        if ($conn->delete('product_attribute_image', '`id_product_attribute` = '.(int) $this->id) === false) {
            return false;
        }

        if (is_array($idsImage) && count($idsImage)) {
            $sqlValues = [];
            foreach ($idsImage as $value) {
                $value = (int)$value;
                if ($value) {
                    $sqlValues[] = [
                        'id_product_attribute' => (int)$this->id,
                        'id_image' => $value,
                    ];
                }
            }

            if ($sqlValues) {
                $conn->insert('product_attribute_image', $sqlValues);
            }
        }

        return true;
    }

    /**
     * @param int $idLang
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getAttributesName($idLang)
    {
        return Db::readOnly()->getArray(
            (new DbQuery())
                ->select('al.*')
                ->from('product_attribute_combination', 'pac')
                ->innerJoin('attribute_lang', 'al', 'pac.`id_attribute` = al.`id_attribute`')
                ->where('al.`id_lang` = '.(int) $idLang)
                ->where('pac.`id_product_attribute` = '.(int) $this->id)
        );
    }

    /**
     * Returns map of used attributes [attribute group -> attribute id]
     *
     * @return array<int, int>
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getAttributes(): array
    {
        if (is_null($this->attributes)) {
            $this->attributes = [];
            $result = Db::readOnly()->getArray(
                (new DbQuery())
                    ->select('ag.id_attribute_group, a.`id_attribute`')
                    ->from('product_attribute_combination', 'pac')
                    ->innerJoin('attribute', 'a', 'pac.`id_attribute` = a.`id_attribute`')
                    ->innerJoin('attribute_group', 'ag', 'ag.`id_attribute_group` = a.`id_attribute_group`')
                    ->where('pac.`id_product_attribute` = ' . (int)$this->id)
            );
            foreach ($result as $row) {
                $attributeGroupId = (int)$row['id_attribute_group'];
                $attributeId = (int)$row['id_attribute'];
                $this->attributes[$attributeGroupId] = $attributeId;
            }
        }
        return $this->attributes;
    }

    /**
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getColorsAttributes()
    {
        return Db::readOnly()->getArray(
            (new DbQuery())
                ->select('a.`id_attribute`')
                ->from('product_attribute_combination', 'pac')
                ->innerJoin('attribute', 'a', 'pac.`id_attribute` = a.`id_attribute`')
                ->innerJoin('attribute_group', 'ag', 'ag.`id_attribute_group` = a.`id_attribute_group`')
                ->where('pac.`id_product_attribute` = '.(int) $this->id)
                ->where('ag.`is_color_group` = 1')
        );
    }

    /**
     * @param TableSchema $table
     */
    public static function processTableSchema($table)
    {
        if ($table->getNameWithoutPrefix() === 'product_attribute_shop') {
            $table->reorderColumns(['id_product', 'id_product_attribute', 'id_shop']);
        }
    }
}
