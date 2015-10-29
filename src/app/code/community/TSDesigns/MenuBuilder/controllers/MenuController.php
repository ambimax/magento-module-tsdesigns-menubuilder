<?php
/**
 * TSDesigns_MenuBuilder_MenuController
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
class TSDesigns_MenuBuilder_MenuController extends Mage_Adminhtml_Controller_Action
{

	/**
     * Initialize menu from request parameters
     *
     * @return TSDesigns_MenuBuilder_Model_Menu
     */
    protected function _initMenu()
    {
    	if(null === Mage::registry('current_menu')) {
	        $menuId  = (int) $this->getRequest()->getParam('id');
	        $menu    = Mage::getModel('menubuilder/menu')
	            ->setStoreId($this->getRequest()->getParam('store', 0));
	
	        if ($menuId) {
	            $menu->load($menuId);
	        }	

	        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
	            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
	        }
	        $menu->setData('_edit_mode', true);
	
	        Mage::register('current_menu', $menu);
	        //Mage::register('root', $menu);
    	}
        return Mage::registry('current_menu');
    }
    
    /**
     * Node view action
     */
    public function viewAction()
    {
		$menu = $this->_initMenu();
        if ($menu) {

            Mage::getModel('catalog/design')->applyDesign($category, Mage_Catalog_Model_Design::APPLY_FOR_CATEGORY);
            Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            if (!$category->hasChildren()) {
                $update->addHandle('catalog_category_layered_nochildren');
            }

            $this->addActionLayoutHandles();

            $update->addHandle($category->getLayoutUpdateHandle());
            $update->addHandle('CATEGORY_'.$category->getId());



            if ($category->getPageLayout()) {
                    $this->getLayout()->helper('page/layout')
                        ->applyHandle($category->getPageLayout());
            }

            $this->loadLayoutUpdates();

            $update->addUpdate($category->getCustomLayoutUpdate());

            $this->generateLayoutXml()->generateLayoutBlocks();

            if ($category->getPageLayout()) {
                $this->getLayout()->helper('page/layout')
                    ->applyTemplate($category->getPageLayout());
            }

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('categorypath-'.$category->getUrlPath())
                    ->addBodyClass('category-'.$category->getUrlKey());
            }

            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        }
        elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }
        
}