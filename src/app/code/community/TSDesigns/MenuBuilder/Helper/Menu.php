<?php
/**
 * TSDesigns_MenuBuilder_Helper_Menu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Licence that is bundled with 
 * this package in the file LICENSE.txt. It is also available through 
 * the world-wide-web at this URL: http://www.tsdesigns.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tsdesigns.de so we can send you a copy immediately.
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * versions in the future. If you wish to customize Magento or this extension for your
 * needs please refer to http://www.magentocommerce.com or http://www.tsdesigns.de
 * for more information.
 * 
 *
 * @category TSDesigns
 * @package TSDesigns_MenuBuilder
 * @author Tobias Schifftner, TSDesigns
 * @license http://www.tsdesigns.de/license
 * @copyright This software is protected by copyright, (c) 2011 TSDesigns.
 * @version 1.6.0 - 2011-10-21 10:31:26
 *
 */
class TSDesigns_MenuBuilder_Helper_Menu extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CATEGORY_URL_SUFFIX  = 'catalog/seo/category_url_suffix';

    /**
     * Store categories cache
     *
     * @var array
     */
    protected $_menuNodes = array();
    protected $_activeLinks = array();

    /**
     * Cache for category rewrite suffix
     *
     * @var array
     */
    protected $_categoryUrlSuffix = array();

    protected $_parentId = null;
    
    /**
     * Retrieve current store categories
     *
     * @param   boolean|string $sorted
     * @param   boolean $asCollection
     * @return  Varien_Data_Tree_Node_Collection|Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection|array
     */
    public function getMenuNodes($sorted=false, $asCollection=false, $toLoad=true)
    {
        $parent     = $this->getParentId();
        $cacheKey   = sprintf('%d-%d-%d-%d', $parent, $sorted, $asCollection, $toLoad);
        if (isset($this->_menuNodes[$cacheKey])) {
            return $this->_menuNodes[$cacheKey];
        }

        /**
         * Check if parent node of the store still exists
         */
        $menu = Mage::getModel('menubuilder/menu');
        /* @var $menu Mage_Catalog_Model_Category */
        if (!$menu->checkId($parent)) {
            if ($asCollection) {
                return new Varien_Data_Collection();
            }
            return array();
        }

        //$recursionLevel  = max(0, (int) Mage::app()->getStore()->getConfig('catalog/navigation/max_depth'));
        $recursionLevel  = 6;
        $menuNodes = $menu->getNodes($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
		#$menuNodes = $menu->
        $this->_menuNodes[$cacheKey] = $menuNodes;
        return $menuNodes;
    }

    /**
     * Retrieve category url
     *
     * @param   Mage_Catalog_Model_Category $menu
     * @return  string
     */
    public function getCategoryUrl($menu)
    {
        if ($menu instanceof Mage_Catalog_Model_Category) {
            return $menu->getUrl();
        }
        return Mage::getModel('catalog/category')
            ->setData($menu->getData())
            ->getUrl();
    }

    /**
     * Check if a category can be shown
     *
     * @param  Mage_Catalog_Model_Category|int $menu
     * @return boolean
     */
    public function canShow($menu)
    {
        if (is_int($menu)) {
            $menu = Mage::getModel('catalog/category')->load($menu);
        }

        if (!$menu->getId()) {
            return false;
        }

        if (!$menu->getIsActive()) {
            return false;
        }
        if (!$menu->isInRootCategoryList()) {
            return false;
        }

        return true;
    }

	/**
     * Retrieve category rewrite sufix for store
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryUrlSuffix($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!isset($this->_categoryUrlSuffix[$storeId])) {
            $this->_categoryUrlSuffix[$storeId] = Mage::getStoreConfig(self::XML_PATH_CATEGORY_URL_SUFFIX, $storeId);
        }
        return $this->_categoryUrlSuffix[$storeId];
    }

    /**
     * Retrieve clear url for category as parrent
     *
     * @param string $url
     * @param bool $slash
     * @param int $storeId
     *
     * @return string
     */
    public function getCategoryUrlPath($urlPath, $slash = false, $storeId = null)
    {
        if (!$this->getCategoryUrlSuffix($storeId)) {
            return $urlPath;
        }

        if ($slash) {
            $regexp     = '#('.preg_quote($this->getCategoryUrlSuffix($storeId), '#').')/$#i';
            $replace    = '/';
        }
        else {
            $regexp     = '#('.preg_quote($this->getCategoryUrlSuffix($storeId), '#').')$#i';
            $replace    = '';
        }

        return preg_replace($regexp, $replace, $urlPath);
    }
    
    public function setParentId($id)
    {
    	$this->_parentId = (int) $id;
    	return $this;
    }
    
    public function getParentId()
    {
    	return $this->_parentId;
    }
}
