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

use CoreUpdater\CodeCallback;
use CoreUpdater\ObjectModelSchemaBuilder;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;

/**
 * Class InstallModelInstall
 */
class InstallModelInstall extends InstallAbstractModel
{
    const SETTINGS_FILE = 'config/settings.inc.php';

    /**
     * @var string[]|null
     */
    private static $cacheLocalizationPackContent = null;

    /**
     * @var array
     */
    public $xmlLoaderIds;

    /**
     * @var FileLogger
     */
    public $logger;

    /**
     * InstallModelInstall constructor.
     *
     * @throws PrestashopInstallerException
     */
    public function __construct()
    {
        parent::__construct();

        $this->logger = new FileLogger();
        if (is_writable(_PS_ROOT_DIR_.'/log/')) {
            $this->logger->setFilename(_PS_ROOT_DIR_.'/log/'.@date('Ymd').'_installation.log');
        }
    }

    /**
     * Generate settings file
     *
     * @param string $databaseServer
     * @param string $databaseLogin
     * @param string $databasePassword
     * @param string $databaseName
     * @param string $databasePrefix
     *
     * @return bool
     *
     * @throws PrestashopInstallerException
     */
    public function generateSettingsFile($databaseServer, $databaseLogin, $databasePassword, $databaseName, $databasePrefix)
    {
        // Check permissions for settings file
        if (file_exists(_PS_ROOT_DIR_.'/'.self::SETTINGS_FILE) && !is_writable(_PS_ROOT_DIR_.'/'.self::SETTINGS_FILE)) {
            $this->setError($this->language->l('%s file is not writable (check permissions)', self::SETTINGS_FILE));

            return false;
        } elseif (!file_exists(_PS_ROOT_DIR_.'/'.self::SETTINGS_FILE) && !is_writable(_PS_ROOT_DIR_.'/'.dirname(self::SETTINGS_FILE))) {
            $this->setError($this->language->l('%s folder is not writable (check permissions)', dirname(self::SETTINGS_FILE)));

            return false;
        }

        // Generate settings content and write file
        $settingsConstants = [
            '_DB_SERVER_'         => $databaseServer,
            '_DB_NAME_'           => $databaseName,
            '_DB_USER_'           => $databaseLogin,
            '_DB_PASSWD_'         => $databasePassword,
            '_DB_PREFIX_'         => $databasePrefix,
            '_MYSQL_ENGINE_'      => 'InnoDB',
            '_PS_CACHING_SYSTEM_' => 'CacheMemcache',
            '_COOKIE_KEY_'        => Tools::passwdGen(56),
            '_COOKIE_IV_'         => Tools::passwdGen(32),
            '_PS_CREATION_DATE_'  => date('Y-m-d'),
            '_TB_VERSION_'        => _TB_INSTALL_VERSION_,
            '_TB_REVISION_'       => _TB_INSTALL_REVISION_,
            '_TB_BUILD_PHP_'      => _TB_INSTALL_BUILD_PHP_,
            '_PS_VERSION_'        => '1.6.1.999',
        ];

        if (Encryptor::supportsPhpEncryption()) {
            try {
                $secureKey = Key::createNewRandomKey();
                $settingsConstants['_PHP_ENCRYPTION_KEY_'] = $secureKey->saveToAsciiSafeString();
            } catch (EnvironmentIsBrokenException $e) {
                throw new PrestashopInstallerException("Failed to generate encryption key", 0, $e);
            }
        }

        $settingsContent = "<?php\n";

        foreach ($settingsConstants as $constant => $value) {
            $settingsContent .= "define('$constant', '".str_replace('\'', '\\\'', $value)."');\n";
        }

        if (!file_put_contents(_PS_ROOT_DIR_.'/'.self::SETTINGS_FILE, $settingsContent)) {
            $this->setError($this->language->l('Cannot write settings file'));

            return false;
        }

        return true;
    }

    /**
     * @param string|array $errors
     */
    public function setError($errors)
    {
        if (!is_array($errors)) {
            $errors = [$errors];
        }

        parent::setError($errors);

        foreach ($errors as $error) {
            $this->logger->logError($error);
        }
    }

    /**
     * PROCESS : installDatabase
     * Generate settings file and create database structure
     *
     * @param bool $clearDatabase
     * @return bool
     * @throws PrestaShopException
     */
    public function installDatabase($clearDatabase = false)
    {
        // Clear database (only tables with same prefix)
        require_once _PS_ROOT_DIR_.'/'.self::SETTINGS_FILE;

        $conn = Db::createInstance( _DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);

        $collations = $conn->getValue(
            'SELECT `COLLATION_NAME`
             FROM `INFORMATION_SCHEMA`.`COLLATIONS`
             WHERE `COLLATION_NAME` = \'utf8mb4_unicode_ci\'
             AND `CHARACTER_SET_NAME` = \'utf8mb4\';'
        );
        if (!$collations) {
            $this->setError($this->language->l('Your database does not seem to support the collation `utf8mb4_unicode_ci`. Make sure you are using at least MySQL 5.5.3 or MariaDB 5.5'));

            return false;
        }

        $engine = $conn->getValue(
            'SELECT `SUPPORT`
             FROM `INFORMATION_SCHEMA`.`ENGINES`
             WHERE `ENGINE` = \'InnoDB\';'
        );
        if (!in_array(mb_strtolower($engine), ['default', 'yes'])) {
            $this->setError(
                sprintf(
                    $this->language->l(
                        'The InnoDB database engine does not seem to be available. If you are using a MySQL alternative, could you please open an issue on %s? Thank you!'
                    ),
                    '<a href="https://github.com/thirtybees/thirtybees.git" target="_blank">GitHub</a>'
                )
            );

            return false;
        }

        if ($clearDatabase) {
            $this->clearDatabase($conn);
        }

        // Install database structure
        static::loadCoreUpdater();
        $schemaBuilder = new ObjectModelSchemaBuilder();
        try {
            $schema = $schemaBuilder->getSchema();
            foreach ($schema->getTables() as $table) {
                if (! $conn->execute($table->getDDLStatement())) {
                    $this->setError($this->language->l('SQL error on query: <i>%s</i>', $conn->getMsgError()));
                    return false;
                }
            }
            return true;
        } catch (Exception $e) {
            $this->setError($this->language->l('Failed to generate database schema: <i>%s</i>i>', $e->getMessage()));
            return false;
        }
    }

    /**
     * Clear database (only tables with same prefix).
     *
     * @param Db $conn
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function clearDatabase($conn)
    {
        $conn->execute("SET FOREIGN_KEY_CHECKS=0");
        try {
            $result = $conn->getArray('SELECT DISTINCT TABLE_NAME AS t FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=database()');
            $tables = array_column($result, 't');
            foreach ($tables as $table) {
                if (!_DB_PREFIX_ || preg_match('#^' . _DB_PREFIX_ . '#i', $table)) {
                    $conn->execute(('DROP TABLE') . ' `' . $table . '`');
                }
            }
        } finally {
            $conn->execute("SET FOREIGN_KEY_CHECKS=1");
        }
    }

    /**
     * PROCESS : installDefaultData
     * Create default shop and languages
     *
     * @param string $shopName
     * @param int|bool $isoCountry
     * @param bool $allLanguages
     * @param bool $clearDatabase
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function installDefaultData($shopName, $isoCountry = false, $allLanguages = false, $clearDatabase = false)
    {
        // Install first shop
        if (!$this->createShop($shopName)) {
            return false;
        }

        // Install languages
        try {
            if (!$allLanguages) {
                $isoCodesToInstall = [$this->language->getLanguageIso()];
                if ($isoCountry) {
                    $version = str_replace('.', '', _TB_VERSION_);
                    $version = substr($version, 0, 2);
                    $localizationFileContent = $this->getLocalizationPackContent($version, $isoCountry);

                    if ($xml = @simplexml_load_string($localizationFileContent)) {
                        foreach ($xml->languages->language as $language) {
                            $isoCodesToInstall[] = (string) $language->attributes()->iso_code;
                        }
                    }
                }
            } else {
                $isoCodesToInstall = null;
            }
            $isoCodesToInstall = array_flip(array_flip($isoCodesToInstall));
            $languages = $this->installLanguages($isoCodesToInstall);
        } catch (PrestashopInstallerException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $flipLanguages = array_flip($languages);
        $idLang = (!empty($flipLanguages[$this->language->getLanguageIso()])) ? $flipLanguages[$this->language->getLanguageIso()] : 1;
        Configuration::updateGlobalValue('PS_LANG_DEFAULT', $idLang);
        Configuration::updateGlobalValue('PS_VERSION_DB', _TB_INSTALL_VERSION_);
        Configuration::updateGlobalValue('PS_INSTALL_VERSION', _TB_INSTALL_VERSION_);

        return true;
    }

    /**
     * @param string $shopName
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createShop($shopName)
    {
        // Create default group shop
        $shopGroup = new ShopGroup();
        $shopGroup->name = 'Default';
        $shopGroup->active = true;
        if (!$shopGroup->add()) {
            $this->setError($this->language->l('Cannot create group shop').' / '.Db::getInstance()->getMsgError());

            return false;
        }

        // Create default shop
        $shop = new Shop();
        $shop->active = true;
        $shop->id_shop_group = $shopGroup->id;
        $shop->id_category = 2;
        $shop->id_theme = 1;
        $shop->name = $shopName;
        if (!$shop->add()) {
            $this->setError($this->language->l('Cannot create shop').' / '.Db::getInstance()->getMsgError());

            return false;
        }
        Context::getContext()->shop = $shop;

        // Create default shop URL
        $shopUrl = new ShopUrl();
        $shopUrl->domain = Tools::getHttpHost();
        $shopUrl->domain_ssl = Tools::getHttpHost();
        $shopUrl->physical_uri = __PS_BASE_URI__;
        $shopUrl->id_shop = $shop->id;
        $shopUrl->main = true;
        $shopUrl->active = true;
        if (!$shopUrl->add()) {
            $this->setError($this->language->l('Cannot create shop URL').' / '.Db::getInstance()->getMsgError());

            return false;
        }

        return true;
    }

    /**
     * @param string $version
     * @param string $country
     *
     * @return string|false
     */
    public function getLocalizationPackContent($version, $country)
    {
        if (InstallModelInstall::$cacheLocalizationPackContent === null || array_key_exists($country, InstallModelInstall::$cacheLocalizationPackContent)) {
            $pathCacheFile = _PS_CACHE_DIR_.'sandbox'.DIRECTORY_SEPARATOR.$version.$country.'.xml';

            $localizationFile = _PS_ROOT_DIR_.'/localization/default.xml';
            if (file_exists(_PS_ROOT_DIR_.'/localization/'.$country.'.xml')) {
                $localizationFile = _PS_ROOT_DIR_.'/localization/'.$country.'.xml';
            }

            $localizationFileContent = file_get_contents($localizationFile);

            file_put_contents($pathCacheFile, $localizationFileContent);

            InstallModelInstall::$cacheLocalizationPackContent[$country] = $localizationFileContent;
        }

        return InstallModelInstall::$cacheLocalizationPackContent[$country] ?? false;
    }

    /**
     * Install languages
     *
     * @param array|null $languagesList
     * @return array Association between ID and iso array(id_lang => iso, ...)
     *
     * @throws PrestaShopException
     * @throws PrestashopInstallerException
     */
    public function installLanguages($languagesList = null)
    {
        if ($languagesList == null || !is_array($languagesList) || !count($languagesList)) {
            $languagesList = $this->language->getIsoList();
        }

        $languagesAvailable = $this->language->getIsoList();
        $languages = [];
        foreach ($languagesList as $iso) {
            if (!in_array($iso, $languagesAvailable)) {
                continue;
            }
            if (!file_exists(_PS_INSTALL_LANGS_PATH_.$iso.'/language.xml')) {
                throw new PrestashopInstallerException($this->language->l('File "language.xml" not found for language iso "%s"', $iso));
            }

            if (!$xml = @simplexml_load_file(_PS_INSTALL_LANGS_PATH_.$iso.'/language.xml')) {
                throw new PrestashopInstallerException($this->language->l('File "language.xml" not valid for language iso "%s"', $iso));
            }

            $paramsLang = [
                'name'                     => (string) $xml->name,
                'iso_code'                 => substr((string) $xml->language_code, 0, 2),
                'language_code'            => (string) $xml->language_code,
                'allow_accented_chars_url' => (string) $xml->allow_accented_chars_url,
            ];

            $errors = Language::downloadAndInstallLanguagePack($iso, _TB_INSTALL_VERSION_, $paramsLang);
            if (is_array($errors)) {
                $installed = false;
                $name = ($xml->name) ? $xml->name : $iso;

                $this->setError($this->language->l('Translations for %s and thirty bees version %s not found.', $name, _TB_INSTALL_VERSION_));
                $this->setError($errors);

                $version = array_map('intval', explode('.', _TB_INSTALL_VERSION_, 3));
                if (isset($version[2]) && $version[2] > 0) {
                    $version[2]--;
                    $version = implode('.', $version);

                    $errors = Language::downloadAndInstallLanguagePack($iso, $version, $paramsLang);
                    if (is_array($errors)) {
                        $this->setError($this->language->l('Translations for thirty bees version %s not found either.', $version));
                        $this->setError($errors);
                    } else {
                        $installed = true;
                        $this->setError($this->language->l('Installed translations for thirty bees version %s instead.', $version));
                    }
                }

                if (!$installed) {
                    $this->setError($this->language->l('Translations for %s not installed. You can catch up on this later.', $name));

                    // XML is actually (almost) a language pack.
                    $xml->name = (string) $xml->name;
                    $xml->is_rtl = filter_var($xml->is_rtl, FILTER_VALIDATE_BOOLEAN);

                    Language::checkAndAddLanguage($iso, $xml, true, $paramsLang);
                }
            }

            Language::loadLanguages();
            Tools::clearCache();
            if (!$idLang = Language::getIdByIso($iso, true)) {
                throw new PrestashopInstallerException($this->language->l('Cannot install language "%s"', ($xml->name) ? $xml->name : $iso));
            }
            $languages[$idLang] = $iso;

            // Copy language flag
            if (is_writable(_PS_IMG_DIR_.'l/')) {
                $hardcodedImageExtension = 'jpg';
                if (!copy(_PS_INSTALL_LANGS_PATH_.$iso.'/flag.'.$hardcodedImageExtension, _PS_IMG_DIR_.'l/'.$idLang.'.'.$hardcodedImageExtension)) {
                    throw new PrestashopInstallerException($this->language->l('Cannot copy flag language "%s"', _PS_INSTALL_LANGS_PATH_.$iso.'/flag.'.$hardcodedImageExtension.' => '._PS_IMG_DIR_.'l/'.$idLang.'.'.$hardcodedImageExtension));
                }
            }
        }

        return $languages;
    }

    /**
     * PROCESS : populateDatabase
     * Populate database with default data
     *
     * @param string|string[]|null $entity
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws PrestashopInstallerException
     */
    public function populateDatabase($entity = null)
    {
        $languages = [];
        foreach (Language::getLanguages(true) as $lang) {
            $languages[$lang['id_lang']] = $lang['iso_code'];
        }

        // Install XML data (data/xml/ folder)
        $xmlLoader = new InstallXmlLoader();
        $xmlLoader->setLanguages($languages);

        if (isset($this->xmlLoaderIds) && $this->xmlLoaderIds) {
            $xmlLoader->setIds($this->xmlLoaderIds);
        }

        if ($entity) {
            if (is_array($entity)) {
                foreach ($entity as $item) {
                    $xmlLoader->populateEntity($item);
                }
            } else {
                $xmlLoader->populateEntity($entity);
            }
        } else {
            $xmlLoader->populateFromXmlFiles();
        }
        if ($errors = $xmlLoader->getErrors()) {
            $this->setError($errors);

            return false;
        }

        // IDS from xmlLoader are stored in order to use them for fixtures
        $this->xmlLoaderIds = $xmlLoader->getIds();
        unset($xmlLoader);

        // Install custom SQL data (db_data.sql file)
        if (file_exists(_PS_INSTALL_DATA_PATH_.'db_data.sql')) {
            $sqlLoader = new InstallSqlLoader();
            $sqlLoader->setMetaData(
                [
                    'PREFIX_'     => _DB_PREFIX_,
                    'ENGINE_TYPE' => _MYSQL_ENGINE_,
                ]
            );

            $sqlLoader->parseFile(_PS_INSTALL_DATA_PATH_.'db_data.sql', false);
            if ($errors = $sqlLoader->getErrors()) {
                $this->setError($errors);

                return false;
            }
        }

        // Copy language default images (we do this action after database in populated because we need image types information)
        foreach ($languages as $iso) {
            $this->copyLanguageImages($iso);
        }

        return true;
    }

    /**
     * @param string $iso
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function copyLanguageImages($iso)
    {
        $dstPath = _PS_IMG_DIR_.'l/';

        if (!$sourceImage = ImageManager::getSourceImage(_PS_IMG_DIR_.'/flags/', $iso, 'png', false)) {
            $sourceImage = _PS_INSTALL_LANGS_PATH_."$iso/flag.jpg";
        }

        $imageExtension = ImageManager::getDefaultImageExtension();
        $newSourceImage = $dstPath.$iso.'.'.$imageExtension;
        ImageManager::convertImageToExtension($sourceImage, $imageExtension, $newSourceImage);

        Language::regenerateDefaultImages($iso);
    }

    /**
     * PROCESS : configureShop
     * Set default shop configuration
     *
     * @throws PrestaShopException
     */
    public function configureShop(array $data = [], array $config = []): bool
    {
        //clear image cache in tmp folder
        if (file_exists(_PS_TMP_IMG_DIR_)) {
            foreach (scandir(_PS_TMP_IMG_DIR_) as $file) {
                if ($file[0] != '.' && $file != 'index.php') {
                    Tools::deleteFile(_PS_TMP_IMG_DIR_.$file);
                }
            }
        }

        $defaultData = [
            'shopName'       => 'My Shop',
            'shopActivity'   => '',
            'shopCountry'    => 'us',
            'shopTimezone'   => 'US/Eastern',
            'rewriteEngine'  => false,
            'sslEnabled'     => false,
        ];

        foreach ($defaultData as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }

        Context::getContext()->shop = new Shop(1);
        Configuration::loadConfiguration();

        $idCountry = (int) Country::getByIso($data['shopCountry']);

        // Set default configuration
        Configuration::updateGlobalValue('PS_SHOP_DOMAIN', Tools::getHttpHost());
        Configuration::updateGlobalValue('PS_SHOP_DOMAIN_SSL', Tools::getHttpHost());
        Configuration::updateGlobalValue('PS_INSTALL_VERSION', _TB_INSTALL_VERSION_);
        Configuration::updateGlobalValue('PS_LOCALE_LANGUAGE', $this->language->getLanguageIso());
        Configuration::updateGlobalValue('PS_SHOP_NAME', $data['shopName']);
        Configuration::updateGlobalValue('PS_SHOP_ACTIVITY', $data['shopActivity']);
        Configuration::updateGlobalValue('PS_COUNTRY_DEFAULT', $idCountry);
        Configuration::updateGlobalValue('PS_LOCALE_COUNTRY', $data['shopCountry']);
        Configuration::updateGlobalValue('PS_TIMEZONE', $data['shopTimezone']);
        Configuration::updateGlobalValue('PS_CONFIGURATION_AGREMENT', (int) $data['configurationAgreement']);

        // Set SSL options
        Configuration::updateGlobalValue('PS_SSL_ENABLED', $data['sslEnabled'] ? 1 : 0);

        // Set default rewriting settings
        Configuration::updateGlobalValue('PS_REWRITING_SETTINGS', $data['rewriteEngine'] ? 1 : 0);

        // Choose the best ciphering algorithm available
        Configuration::updateGlobalValue('PS_CIPHER_ALGORITHM', $this->getCipherAlgorightm());

        $groups = Group::getGroups((int) Configuration::get('PS_LANG_DEFAULT'));
        $conn = Db::getInstance();
        $groupsDefault = $conn->getArray('SELECT `name` FROM '._DB_PREFIX_.'configuration WHERE `name` LIKE "PS_%_GROUP" ORDER BY `id_configuration`');
        foreach ($groupsDefault as &$groupDefault) {
            if (is_array($groupDefault) && isset($groupDefault['name'])) {
                $groupDefault = $groupDefault['name'];
            }
        }

        if (is_array($groups) && count($groups)) {
            foreach ($groups as $key => $group) {
                if (Configuration::get($groupsDefault[$key]) != $group['id_group']) {
                    Configuration::updateGlobalValue($groupsDefault[$key], (int) $group['id_group']);
                }
            }
        }

        $states = $conn->getArray('SELECT `id_order_state` FROM '._DB_PREFIX_.'order_state ORDER BY `id_order_state`');
        $statesDefault = $conn->getArray('SELECT MIN(`id_configuration`), `name` FROM '._DB_PREFIX_.'configuration WHERE `name` LIKE "PS_OS_%" GROUP BY `value` ORDER BY`id_configuration`');

        foreach ($statesDefault as &$stateDefault) {
            if (is_array($stateDefault) && isset($stateDefault['name'])) {
                $stateDefault = $stateDefault['name'];
            }
        }

        if (count($states)) {
            foreach ($states as $key => $state) {
                if (Configuration::get($statesDefault[$key]) != $state['id_order_state']) {
                    Configuration::updateGlobalValue($statesDefault[$key], (int) $state['id_order_state']);
                }
            }
            /* deprecated order state */
            Configuration::updateGlobalValue('PS_OS_OUTOFSTOCK_PAID', (int) Configuration::get('PS_OS_OUTOFSTOCK'));
        }

        // Set logo configuration
        if ($sourceImage = ImageManager::getSourceImage(_PS_IMG_DIR_, 'logo')) {
            list($width, $height) = getimagesize($sourceImage);
            Configuration::updateGlobalValue('SHOP_LOGO_WIDTH', round($width));
            Configuration::updateGlobalValue('SHOP_LOGO_HEIGHT', round($height));
        }

        Configuration::updateGlobalValue('PS_SMARTY_CACHE', 1);

        // Active only the country selected by the merchant
        $conn->execute('UPDATE '._DB_PREFIX_.'country SET active = 0 WHERE id_country != '.(int) $idCountry);

        // Set localization configuration
        $version = str_replace('.', '', _TB_VERSION_);
        $version = substr($version, 0, 2);
        $localizationFileContent = $this->getLocalizationPackContent($version, $data['shopCountry']);

        $locale = new LocalizationPack();
        $locale->loadLocalisationPack($localizationFileContent, [], true);

        // Create default employee
        if (isset($data['adminFirstname']) && isset($data['adminLastname']) && isset($data['adminPassword']) && isset($data['adminEmail'])) {
            $employee = new Employee();
            $employee->firstname = Tools::ucfirst($data['adminFirstname']);
            $employee->lastname = Tools::ucfirst($data['adminLastname']);
            $employee->email = $data['adminEmail'];
            $employee->passwd = Tools::hash($data['adminPassword']);
            $employee->last_passwd_gen = date('Y-m-d H:i:s', strtotime('-360 minutes'));
            $employee->bo_theme = 'default';
            $employee->bo_css = 'schemes/admin-theme-thirtybees.css';
            $employee->default_tab = 1;
            $employee->active = true;
            $employee->optin = true;
            $employee->id_profile = 1;
            $employee->id_lang = Configuration::get('PS_LANG_DEFAULT');
            $employee->bo_menu = 1;
            if (!$employee->add()) {
                $this->setError($this->language->l('Cannot create admin account'));

                return false;
            }

            // Update default contact
            Configuration::updateGlobalValue('PS_SHOP_EMAIL', $data['adminEmail']);

            $contacts = new PrestaShopCollection('Contact');
            foreach ($contacts as $contact) {
                /** @var Contact $contact */
                $contact->email = $data['adminEmail'];
                $contact->update();
            }

            if (!@Tools::generateHtaccess(null, $data['rewriteEngine'])) {
                Configuration::updateGlobalValue('PS_REWRITING_SETTINGS', 0);
            }

            foreach ($config as $key => $value) {
                Configuration::updateGlobalValue($key, $value);
            }

            return true;
        } else {
            $this->setError($this->language->l('Cannot create admin account'));
            return false;
        }
    }

    /**
     * PROCESS : installModules
     *
     * @param string|string[]|null $module
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function installModules($module = null)
    {
        if ($module && !is_array($module)) {
            $module = [$module];
        }

        $modules = $module ? $module : $this->getModulesList();

        Module::updateTranslationsAfterInstall(false);

        $errors = [];
        foreach ($modules as $moduleName) {
            if (!file_exists(_PS_MODULE_DIR_.$moduleName.'/'.$moduleName.'.php')) {
                continue;
            }

            $module = Module::getInstanceByName($moduleName);
            if (!$module->install()) {
                $errors[] = $this->language->l('Cannot install module "%s"', $moduleName);
            }
        }

        if ($errors) {
            $this->setError($errors);

            return false;
        }

        Module::updateTranslationsAfterInstall(true);
        Language::updateModulesTranslations($modules);

        return true;
    }

    /**
     * @return string[] List of modules to install.
     */
    public function getModulesList()
    {
        return require(_PS_CORE_DIR_ . '/config/default_modules.php');
    }

    /**
     * PROCESS : installFixtures
     * Install fixtures (E.g. demo products)
     *
     * @param string|string[]|null $entity
     * @param array $data
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws PrestashopInstallerException
     */
    public function installFixtures($entity = null, array $data = [])
    {
        $fixturesPath = _PS_INSTALL_FIXTURES_PATH_.'thirtybees/';
        $fixturesName = 'thirtybees';

        // Load class (use fixture class if one exists, or use InstallXmlLoader)
        if (file_exists($fixturesPath.'/install.php')) {
            require_once $fixturesPath.'/install.php';
            $class = 'InstallFixtures'.Tools::toCamelCase($fixturesName);
            if (!class_exists($class, false)) {
                $this->setError($this->language->l('Fixtures class "%s" not found', $class));

                return false;
            }

            $xmlLoader = new $class();
            if (!$xmlLoader instanceof InstallXmlLoader) {
                $this->setError($this->language->l('"%s" must be an instance of "InstallXmlLoader"', $class));

                return false;
            }
        } else {
            $xmlLoader = new InstallXmlLoader();
        }

        $languages = [];
        foreach (Language::getLanguages(false) as $lang) {
            $languages[$lang['id_lang']] = $lang['iso_code'];
        }
        $xmlLoader->setLanguages($languages);

        // Install XML data (data/xml/ folder)
        if (isset($this->xmlLoaderIds) && $this->xmlLoaderIds) {
            $xmlLoader->setIds($this->xmlLoaderIds);
        } else {
            // Load from default path, stuff for populateDatabase().
            $xmlLoader->populateFromXmlFiles(false);
        }

        // Switch to fixtures path.
        $xmlLoader->setFixturesPath($fixturesPath);

        if ($entity) {
            if (is_array($entity)) {
                foreach ($entity as $item) {
                    $xmlLoader->populateEntity($item);
                }
            } else {
                $xmlLoader->populateEntity($entity);
            }
        } else {
            $xmlLoader->populateFromXmlFiles();
        }

        if ($errors = $xmlLoader->getErrors()) {
            $this->setError($errors);

            return false;
        }

        // Store IDs for the next run of this method.
        $this->xmlLoaderIds = $xmlLoader->getIds();
        unset($xmlLoader);

        // Index products in search tables
        Search::indexation(true);

        return true;
    }

    /**
     * PROCESS : initializeClasses
     *
     * Executes initialization callbacks on all classes that implements the interface
     *
     * @return bool
     */
    public function initializeClasses()
    {
        static::loadCoreUpdater();
        try {
            $callback = new CodeCallback();
            $callback->execute(Db::getInstance());
            return true;
        } catch (Exception $e) {
            $this->setError($this->language->l('Failed to initialize classes: %s', $e->getMessage()));
            return false;

        }
    }

    /**
     * PROCESS : installTheme
     * Install theme
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws PrestashopInstallerException
     */
    public function installTheme()
    {
        $theme = Theme::installFromDir(_PS_ALL_THEMES_DIR_._THEME_NAME_);
        if (Validate::isLoadedObject($theme)) {
            // Never returns an error.
            $theme->installIntoShopContext();
        } else {
            $this->setError($this->language->l('Failed to import theme.'));
            $this->setError($theme);

            return false;
        }

        // Override some module defaults to fit the default theme.
        $sqlLoader = new InstallSqlLoader();
        $sqlLoader->setMetaData(
            [
                'PREFIX_'     => _DB_PREFIX_,
                'ENGINE_TYPE' => _MYSQL_ENGINE_,
            ]
        );

        $sqlLoader->parseFile(_PS_INSTALL_DATA_PATH_.'theme.sql', false);
        if ($errors = $sqlLoader->getErrors()) {
            $this->setError($errors);

            return false;
        }

        return true;
    }

    /**
     * Returns best ciphering algorithm available for current environment
     *
     * @return int
     */
    public function getCipherAlgorightm()
    {
        return Encryptor::supportsPhpEncryption()
            ? Encryptor::ALGO_PHP_ENCRYPTION
            : Encryptor::ALGO_BLOWFISH;
    }

    /**
     * Includes core updater classes
     *
     * @return void
     */
    protected static function loadCoreUpdater()
    {
        $dir = _PS_MODULE_DIR_ . 'coreupdater/';
        if (! file_exists($dir)) {
            throw new RuntimeException('Core updater is not part of the installation package!');
        }
        require_once($dir . 'classes/schema/autoload.php');
        require_once($dir . 'classes/CodeCallback.php');
    }
}
