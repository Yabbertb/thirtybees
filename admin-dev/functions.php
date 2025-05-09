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
 *  @author    thirty bees <contact@thirtybees.com>
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2017-2024 thirty bees
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */
if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}
require_once(_PS_ADMIN_DIR_.'/../images.inc.php');

/**
 * @param string $id
 * @param bool $time
 * @return void
 */
function bindDatepicker($id, $time)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    if ($time) {
        echo '
		var dateObj = new Date();
		var hours = dateObj.getHours();
		var mins = dateObj.getMinutes();
		var secs = dateObj.getSeconds();
		if (hours < 10) { hours = "0" + hours; }
		if (mins < 10) { mins = "0" + mins; }
		if (secs < 10) { secs = "0" + secs; }
		var time = " "+hours+":"+mins+":"+secs;';
    }

    echo '
	$(function() {
		$("#'.Tools::htmlentitiesUTF8($id).'").datepicker({
			prevText:"",
			nextText:"",
			dateFormat:"yy-mm-dd"'.($time ? '+time' : '').'});
	});';
}

/**
 * @param int|array $id ID can be a identifier or an array of identifiers
 * @param bool $time
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 * @deprecated 1.5.3.0 Use Controller::addJqueryUi('ui.datepicker') instead
 */
function includeDatepicker($id, $time = false)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/ui/jquery.ui.core.min.js"></script>';
    echo '<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'js/jquery/ui/themes/ui-lightness/jquery.ui.theme.css" />';
    echo '<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'js/jquery/ui/themes/ui-lightness/jquery.ui.datepicker.css" />';
    $iso = Db::readOnly()->getValue('SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE `id_lang` = '.(int)Context::getContext()->language->id);
    if ($iso != 'en') {
        echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/ui/i18n/jquery.ui.datepicker-'.Tools::htmlentitiesUTF8($iso).'.js"></script>';
    }
    echo '<script type="text/javascript">';
    if (is_array($id)) {
        foreach ($id as $id2) {
            bindDatepicker($id2, $time);
        }
    } else {
        bindDatepicker($id, $time);
    }
    echo '</script>';
}

/**
 * Cast a number to a price.
 *
 * @param float|int|string $price The price to cast.
 *
 * @return float Casted price, rounded to _TB_PRICE_DATABASE_PRECISION_.
 */
function priceval($price)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    return Tools::parseNumber($price);
}

/**
 * Generate a new settings file, only transmitted parameters are updated
 *
 * @param string $base_urls Base URI
 * @param string $theme Theme name (eg. default)
 * @param array $array_db Parameters in order to connect to database
 * @deprecated 1.4.0
 * @return bool
 */
function rewriteSettingsFile($base_urls = null, $theme = null, $array_db = null)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    return false;
}

/**
 * Display SQL date in friendly format
 *
 * @param string $sql_date Date in SQL format (YYYY-MM-DD HH:mm:ss)
 * @param bool $with_time Display both date and time
 * @deprecated 1.4.0 Use Tools::displayDate instead
 */
function displayDate($sql_date, $with_time = false)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    return date('Y-m-d'.($with_time ? ' H:i:s' : ''), strtotime($sql_date));
}

/**
 * Return path to a product category
 *
 * @param string $url_base Start URL
 * @param int $id_category Start category
 * @param string $path Current path
 * @param string $highlight String to highlight (in XHTML/CSS)
 * @param string $category_type
 * @param bool $home
 * @return false|string|void
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function getPath($url_base, $id_category, $path = '', $highlight = '', $category_type = 'catalog', $home = false)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    $context = Context::getContext();
    if ($category_type == 'catalog') {
        $conn = Db::readOnly();
        $category = $conn->getRow('
		SELECT id_category, level_depth, nleft, nright
		FROM '._DB_PREFIX_.'category
		WHERE id_category = '.(int)$id_category);
        if (isset($category['id_category'])) {
            $sql = 'SELECT c.id_category, cl.name, cl.link_rewrite
					FROM '._DB_PREFIX_.'category c
					LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = c.id_category'.Shop::addSqlRestrictionOnLang('cl').')
					WHERE c.nleft <= '.(int)$category['nleft'].'
						AND c.nright >= '.(int)$category['nright'].'
						AND cl.id_lang = '.(int)$context->language->id.
                        ($home ? ' AND c.id_category='.(int)$id_category : '').'
						AND c.id_category != '.(int)Category::getTopCategory()->id.'
					GROUP BY c.id_category
					ORDER BY c.level_depth ASC
					LIMIT '.(!$home ? (int)$category['level_depth'] + 1 : 1);
            $categories = $conn->getArray($sql);
            $full_path = '';
            $n = 1;
            $n_categories = (int)count($categories);
            foreach ($categories as $category) {
                $link = Context::getContext()->link->getAdminLink('AdminCategories');
                $edit = '<a href="'.Tools::safeOutput($link.'&id_category='.(int)$category['id_category'].'&'.(($category['id_category'] == 1 || $home) ? 'viewcategory' : 'updatecategory')).'" title="'.($category['id_category'] == Category::getRootCategory()->id_category ? 'Home' : 'Modify').'"><i class="icon-'.(($category['id_category'] == Category::getRootCategory()->id_category || $home) ? 'home' : 'pencil').'"></i></a> ';
                $full_path .= $edit.
                ($n < $n_categories ? '<a href="'.Tools::safeOutput($url_base.'&id_category='.(int)$category['id_category'].'&viewcategory&token='.Tools::getAdminToken('AdminCategories'.(int)Tab::getIdFromClassName('AdminCategories').(int)$context->employee->id)).'" title="'.htmlentities($category['name'], ENT_NOQUOTES, 'UTF-8').'">' : '').
                (!empty($highlight) ? str_ireplace($highlight, '<span class="highlight">'.htmlentities($highlight, ENT_NOQUOTES, 'UTF-8').'</span>', $category['name']) : $category['name']).
                ($n < $n_categories ? '</a>' : '').
                (($n++ != $n_categories || !empty($path)) ? ' > ' : '');
            }

            return $full_path.$path;
        }
    } elseif ($category_type == 'cms') {
        $category = new CMSCategory($id_category, $context->language->id);
        if (!$category->id) {
            return $path;
        }

        $name = ($highlight != null) ? str_ireplace($highlight, '<span class="highlight">'.$highlight.'</span>', CMSCategory::hideCMSCategoryPosition($category->name)) : CMSCategory::hideCMSCategoryPosition($category->name);
        $edit = '<a href="'.Tools::safeOutput($url_base.'&id_cms_category='.$category->id.'&addcategory&token='.Tools::getAdminToken('AdminCmsContent'.(int)Tab::getIdFromClassName('AdminCmsContent').(int)$context->employee->id)).'">
				<i class="icon-pencil"></i></a> ';
        if ($category->id == 1) {
            $edit = '<li><a href="'.Tools::safeOutput($url_base.'&id_cms_category='.$category->id.'&viewcategory&token='.Tools::getAdminToken('AdminCmsContent'.(int)Tab::getIdFromClassName('AdminCmsContent').(int)$context->employee->id)).'">
					<i class="icon-home"></i></a></li> ';
        }
        $path = $edit.'<li><a href="'.Tools::safeOutput($url_base.'&id_cms_category='.$category->id.'&viewcategory&token='.Tools::getAdminToken('AdminCmsContent'.(int)Tab::getIdFromClassName('AdminCmsContent').(int)$context->employee->id)).'">
		'.$name.'</a></li> > '.$path;
        if ($category->id == 1) {
            return substr($path, 0, strlen($path) - 3);
        }
        return getPath($url_base, $category->id_parent, $path, '', 'cms');
    }
}

/**
 * @param string $path
 * @return array
 */
function getDirContent($path)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    $content = [];
    if (is_dir($path)) {
        $d = dir($path);
        while (false !== ($entry = $d->read())) {
            if ($entry[0] != '.') {
                $content[] = $entry;
            }
        }
        $d->close();
    }
    return $content;
}

/**
 * @param string $path
 * @param int $rights
 * @return bool
 */
function createDir($path, $rights)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    if (file_exists($path)) {
        return true;
    }
    return @mkdir($path, $rights);
}

/**
 * @deprecated 1.5.4.1 Use Translate::getAdminTranslation($string) instead
 * @param string $string
 * @return string
 */
function translate($string)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    global $_LANGADM;
    if (!is_array($_LANGADM)) {
        return str_replace('"', '&quot;', $string);
    }
    $key = 'index' . md5(str_replace('\'', '\\\'', $string));
    $str = (array_key_exists($key, $_LANGADM) && $_LANGADM[$key] !== '') ? $_LANGADM[$key] : $string;
    return str_replace('"', '&quot;', stripslashes($str));
}

/**
 * Returns a new Tab object
 *
 * @param string $tab class name
 * @return AdminTab|bool tab object or false if failed
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function checkingTab($tab)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    if (!Validate::isTabName($tab)) {
        return false;
    }
    $row = Db::readOnly()->getRow('SELECT id_tab, module, class_name FROM `'._DB_PREFIX_.'tab` WHERE LOWER(class_name) = \''.pSQL($tab).'\'');
    if (!$row['id_tab']) {
        if (isset(AdminTab::$tabParenting[$tab])) {
            Tools::redirectAdmin('?tab='.AdminTab::$tabParenting[$tab].'&token='.Tools::getAdminTokenLite(AdminTab::$tabParenting[$tab]));
        }
        echo sprintf(Tools::displayError('Page %s cannot be found.'), $tab);
        return false;
    }

    // Class file is included in Dispatcher::dispatch() function
    if (!class_exists($tab, false)) {
        echo sprintf(Tools::displayError('The class %s cannot be found.'), $tab);
        return false;
    }
    $admin_obj = new $tab;
    if (!$admin_obj->viewAccess() && ($admin_obj->table != 'employee' || Context::getContext()->employee->id != Tools::getIntValue('id_employee') || !Tools::isSubmit('updateemployee'))) {
        $admin_obj->_errors = [Tools::displayError('Access denied.')];
        echo $admin_obj->displayErrors();
        return false;
    }
    return $admin_obj;
}

/**
 * @param int $id_tab
 * @return bool
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function checkTabRights($id_tab)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    return Context::getContext()->employee->hasAccess($id_tab, Profile::PERMISSION_VIEW);
}


/**
 * Converts a simpleXML element into an array. Preserves attributes and everything.
 * You can choose to get your elements either flattened, or stored in a custom index that
 * you define.
 * For example, for a given element
 * <field name="someName" type="someType"/>
 * if you choose to flatten attributes, you would get:
 * $array['field']['name'] = 'someName';
 * $array['field']['type'] = 'someType';
 * If you choose not to flatten, you get:
 * $array['field']['@attributes']['name'] = 'someName';
 * _____________________________________
 * Repeating fields are stored in indexed arrays. so for a markup such as:
 * <parent>
 * <child>a</child>
 * <child>b</child>
 * <child>c</child>
 * </parent>
 * you array would be:
 * $array['parent']['child'][0] = 'a';
 * $array['parent']['child'][1] = 'b';
 * ...And so on.
 * _____________________________________
 * @param simpleXMLElement $xml the XML to convert
 * @param bool $flatten_values Choose wether to flatten values
 *                                    or to set them under a particular index.
 *                                    defaults to true;
 * @param bool $flatten_attributes Choose wether to flatten attributes
 *                                    or to set them under a particular index.
 *                                    Defaults to true;
 * @param bool $flatten_children Choose wether to flatten children
 *                                    or to set them under a particular index.
 *                                    Defaults to true;
 * @param string $value_key index for values, in case $flatten_values was set to false. Defaults to "@value"
 * @param string $attributes_key index for attributes, in case $flatten_attributes was set to false. Defaults to "@attributes"
 * @param string $children_key index for children, in case $flatten_children was set to false. Defaults to "@children"
 * @return array the resulting array.
 */
function simpleXMLToArray($xml, $flatten_values = true, $flatten_attributes = true, $flatten_children = true, $value_key = '@value', $attributes_key = '@attributes', $children_key = '@children')
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    $return = [];
    if (!($xml instanceof SimpleXMLElement)) {
        return $return;
    }

    $name = $xml->getName();
    $value = trim((string)$xml);
    if (strlen($value) == 0) {
        $value = null;
    }

    if ($value !== null) {
        if (!$flatten_values) {
            $return[$value_key] = $value;
        } else {
            $return = $value;
        }
    }

    $children = [];
    $first = true;
    foreach ($xml->children() as $element_name => $child) {
        $value = simpleXMLToArray($child, $flatten_values, $flatten_attributes, $flatten_children, $value_key, $attributes_key, $children_key);
        if (isset($children[$element_name])) {
            if ($first) {
                $temp = $children[$element_name];
                unset($children[$element_name]);
                $children[$element_name][] = $temp;
                $first = false;
            }
            $children[$element_name][] = $value;
        } else {
            $children[$element_name] = $value;
        }
    }

    if (count($children) > 0) {
        if (!$flatten_children) {
            $return[$children_key] = $children;
        } else {
            $return = array_merge($return, $children);
        }
    }

    $attributes = [];
    foreach ($xml->attributes() as $name => $value) {
        $attributes[$name] = trim($value);
    }

    if (count($attributes) > 0) {
        if (!$flatten_attributes) {
            $return[$attributes_key] = $attributes;
        } else {
            $return = array_merge($return, $attributes);
        }
    }

    return $return;
}

/**
 * for retrocompatibility with old AdminTab, old index.php
 *
 * @param string $tab
 * @param bool $ajax_mode
 * @return void
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 * @throws SmartyException
 */
function runAdminTab($tab, $ajax_mode = false)
{
    Tools::displayAsDeprecated(sprintf("Function '%s' will be removed in thirty bees 1.6.0", __FUNCTION__));

    $ajax_mode = (bool)$ajax_mode;

    require_once(_PS_ADMIN_DIR_.'/init.php');
    $cookie = Context::getContext()->cookie;
    if (empty($tab) && !count($_POST)) {
        $tab = 'AdminDashboard';
        $_POST['tab'] = $tab;
        $_POST['token'] = Tools::getAdminTokenLite($tab);
    }
    // $tab = $_REQUEST['tab'];
    if ($admin_obj = checkingTab($tab)) {
        Context::getContext()->controller = $admin_obj;
        // init is different for new tabs (AdminController) and old tabs (AdminTab)
        if ($admin_obj instanceof AdminController) {
            if ($ajax_mode) {
                $admin_obj->ajax = true;
            }
            $admin_obj->path = dirname($_SERVER['PHP_SELF']);
            $admin_obj->run();
        } else {
            if (!$ajax_mode) {
                require_once(_PS_ADMIN_DIR_.'/header.inc.php');
            }
            $iso_user = Context::getContext()->language->id;
            $tabs = Tab::recursiveTab($admin_obj->id);
            $tabs = array_reverse($tabs);
            $bread = '';
            foreach ($tabs as $key => $item) {
                $bread .= ' <img src="../img/admin/separator_breadcrumb.png" style="margin-right:5px" alt="&gt;" />';
                if (count($tabs) - 1 > $key) {
                    $bread .= '<a href="?tab='.$item['class_name'].'&token='.Tools::getAdminToken($item['class_name'].(int)$item['id_tab'].(int)Context::getContext()->employee->id).'">';
                }

                $bread .= $item['name'];
                if (count($tabs) - 1 > $key) {
                    $bread .= '</a>';
                }
            }

            if (!$ajax_mode && Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL && Context::getContext()->controller->multishop_context != Shop::CONTEXT_ALL) {
                echo '<div class="multishop_info">';
                if (Shop::getContext() == Shop::CONTEXT_GROUP) {
                    $shop_group = new ShopGroup((int)Shop::getContextShopGroupID());
                    printf(Translate::getAdminTranslation('You are configuring your store for group shop %s'), '<b>'.$shop_group->name.'</b>');
                } elseif (Shop::getContext() == Shop::CONTEXT_SHOP) {
                    printf(Translate::getAdminTranslation('You are configuring your store for shop %s'), '<b>'.Context::getContext()->shop->name.'</b>');
                }
                echo '</div>';
            }
            if (Validate::isLoadedObject($admin_obj)) {
                if ($admin_obj->checkToken()) {
                    if ($ajax_mode) {
                        // the differences with index.php is here
                        $admin_obj->ajaxPreProcess();
                        $action = Tools::getValue('action');
                        // no need to use displayConf() here

                        if (!empty($action) && method_exists($admin_obj, 'ajaxProcess'.Tools::toCamelCase($action))) {
                            $admin_obj->{'ajaxProcess'.Tools::toCamelCase($action)}();
                        } else {
                            $admin_obj->ajaxProcess();
                        }

                        // @TODO We should use a displayAjaxError
                        $admin_obj->displayErrors();
                        if (!empty($action) && method_exists($admin_obj, 'displayAjax'.Tools::toCamelCase($action))) {
                            $admin_obj->{'displayAjax'.$action}();
                        } else {
                            $admin_obj->displayAjax();
                        }
                    } else {
                        /* Filter memorization */
                        if (!empty($_POST) && isset($admin_obj->table)) {
                            foreach ($_POST as $key => $value) {
                                if (is_array($admin_obj->table)) {
                                    foreach ($admin_obj->table as $table) {
                                        if (strncmp($key, $table.'Filter_', 7) === 0 || strncmp($key, 'submitFilter', 12) === 0) {
                                            $cookie->$key = !is_array($value) ? $value : json_encode($value);
                                        }
                                    }
                                } elseif (strncmp($key, $admin_obj->table.'Filter_', 7) === 0 || strncmp($key, 'submitFilter', 12) === 0) {
                                    $cookie->$key = !is_array($value) ? $value : json_encode($value);
                                }
                            }
                        }

                        if (!empty($_GET) && isset($admin_obj->table)) {
                            foreach ($_GET as $key => $value) {
                                if (is_array($admin_obj->table)) {
                                    foreach ($admin_obj->table as $table) {
                                        if (strncmp($key, $table.'OrderBy', 7) === 0 || strncmp($key, $table.'Orderway', 8) === 0) {
                                            $cookie->$key = $value;
                                        }
                                    }
                                } elseif (strncmp($key, $admin_obj->table.'OrderBy', 7) === 0 || strncmp($key, $admin_obj->table.'Orderway', 12) === 0) {
                                    $cookie->$key = $value;
                                }
                            }
                        }
                        $admin_obj->displayConf();
                        $admin_obj->postProcess();
                        $admin_obj->displayErrors();
                        $admin_obj->display();
                        include(_PS_ADMIN_DIR_.'/footer.inc.php');
                    }
                } else {
                    if ($ajax_mode) {
                        // If this is an XSS attempt, then we should only display a simple, secure page
                        if (ob_get_level() && ob_get_length() > 0) {
                            ob_clean();
                        }
                        die(json_encode(Translate::getAdminTranslation('Invalid security token')));
                    } else {
                        // If this is an XSS attempt, then we should only display a simple, secure page
                        if (ob_get_level() && ob_get_length() > 0) {
                            ob_clean();
                        }

                        // ${1} in the replacement string of the regexp is required, because the token may begin with a number and mix up with it (e.g. $17)
                        $url = preg_replace('/([&?]token=)[^&]*(&.*)?$/', '${1}'.$admin_obj->token.'$2', $_SERVER['REQUEST_URI']);
                        if (false === strpos($url, '?token=') && false === strpos($url, '&token=')) {
                            $url .= '&token='.$admin_obj->token;
                        }

                        $message = Translate::getAdminTranslation('Invalid security token');
                        echo '<html><head><title>'.$message.'</title></head><body style="font-family:Arial,Verdana,Helvetica,sans-serif;background-color:#EC8686">
							<div style="background-color:#FAE2E3;border:1px solid #000000;color:#383838;font-weight:700;line-height:20px;margin:0 0 10px;padding:10px 15px;width:500px">
								<img src="../img/admin/error2.png" style="margin:-4px 5px 0 0;vertical-align:middle">
								'.$message.'
							</div>';
                        echo '<a href="'.htmlentities($url).'" method="get" style="float:left;margin:10px">
								<input type="button" value="'.Tools::htmlentitiesUTF8(Translate::getAdminTranslation('I understand the risks and I really want to display this page')).'" style="height:30px;margin-top:5px" />
							</a>
							<a href="index.php" method="get" style="float:left;margin:10px">
								<input type="button" value="'.Tools::htmlentitiesUTF8(Translate::getAdminTranslation('Take me out of here!')).'" style="height:40px" />
							</a>
						</body></html>';
                        die;
                    }
                }
            }
        }
    }
}
