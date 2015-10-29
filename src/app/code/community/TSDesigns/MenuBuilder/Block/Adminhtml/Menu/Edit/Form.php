<?php
/**
 * TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Edit_Form
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
class TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Edit_Form 
	extends TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Abstract
{
    /**
     * Additional buttons on category page
     *
     * @var array
     */
    protected $_additionalButtons = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('menubuilder/menu/edit/form.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('tabs',
            $this->getLayout()->createBlock('menubuilder/adminhtml_menu_edit_tabs', 'tabs')
        );
/*
        $this->setChild('node',
            $this->getLayout()->createBlock('menubuilder/adminhtml_menu_edit_node', 'node')
        );
*/
        if (!$this->getMenu()->isReadonly()) {
            $this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('menubuilder')->__($this->getMenu()->isRoot() ? 'Save Menu' : 'Save Node'),
                        'onclick'   => "menuNodeSubmit('".$this->getSaveUrl()."',true)",
                        'class' => 'save'
                    ))
            );
        }
        if (!in_array($this->getMenu()->getId(), $this->getRootIds()) &&
            $this->getMenu()->isDeleteable()) {
            $this->setChild('delete_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('menubuilder')->__($this->getMenu()->isRoot() ? 'Delete Menu' : 'Delete Node'),
                        'onclick'   => "menuNodeDelete('".$this->getUrl('*/*/delete', array('_current'=>true))."',true)",
                        'class' => 'delete'
                    ))
            );
        }

        if (!$this->getMenu()->isReadonly()) {
            $this->setChild('reset_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('menubuilder')->__('Reset'),
                        'onclick'   => "menuNodeReset('".$this->getUrl('*/*/edit', array('_current'=>true))."',true)"
                    ))
            );
        }

        return parent::_prepareLayout();
    }

    public function getStoreConfigurationUrl()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $params = array();
//        $params = array('section'=>'menubuilder');
        if ($storeId) {
            $store = Mage::app()->getStore($storeId);
            $params['website'] = $store->getWebsite()->getCode();
            $params['store']   = $store->getCode();
        }
        return $this->getUrl('*/system_store', $params);
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getSaveButtonHtml()
    {
        if ($this->hasRootMenu()) {
            return $this->getChildHtml('save_button');
        }
        return '';
    }

    public function getResetButtonHtml()
    {
        if ($this->hasRootMenu()) {
            return $this->getChildHtml('reset_button');
        }
        return '';
    }

    /**
     * Retrieve additional buttons html
     *
     * @return string
     */
    public function getAdditionalButtonsHtml()
    {
        $html = '';
        foreach ($this->_additionalButtons as $childName) {
            $html .= $this->getChildHtml($childName);
        }
        return $html;
    }

    /**
     * Add additional button
     *
     * @param string $alias
     * @param array $config
     * @return Mage_Adminhtml_Block_Catalog_Category_Edit_Form
     */
    public function addAdditionalButton($alias, $config)
    {
        $this->setChild($alias . '_button',
                        $this->getLayout()->createBlock('adminhtml/widget_button')->addData($config));
        $this->_additionalButtons[$alias] = $alias . '_button';
        return $this;
    }

    /**
     * Remove additional button
     *
     * @param string $alias
     * @return Mage_Adminhtml_Block_Catalog_Category_Edit_Form
     */
    public function removeAdditionalButton($alias)
    {
        if (isset($this->_additionalButtons[$alias])) {
            $this->unsetChild($this->_additionalButtons[$alias]);
            unset($this->_additionalButtons[$alias]);
        }

        return $this;
    }

    public function getTabsHtml()
    {
        return $this->getChildHtml('tabs');
    }

    public function getHeader()
    {
        if ($this->hasRootMenu()) {
        	if($this->getMenuId()) {
        		return $this->getMenuName();
        	} else {
        		return Mage::helper('menubuilder')->__($this->getMenu()->isRoot() ? 'New Menu' : 'New Node');
        	}
        }
        return Mage::helper('menubuilder')->__('Set Root Node For Store');
    }

    public function getDeleteUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/delete', $params);
    }

    public function isAjax()
    {
        return Mage::app()->getRequest()->isXmlHttpRequest() || Mage::app()->getRequest()->getParam('isAjax');
    }
}
