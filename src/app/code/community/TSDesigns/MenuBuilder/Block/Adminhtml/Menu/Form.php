<?php
/**
 * TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Form
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
class TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );
        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('adminhtml/catalog_form_renderer_fieldset_element')
        );        
    }
    
    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'colorpicker'   => Mage::getConfig()->getBlockClassName('menubuilder/adminhtml_form_element_colorpicker'),
        	'image' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_category_helper_image'),
            'price'   => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_price'),
            'boolean' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean'),
            'select' => Mage::getConfig()->getBlockClassName('menubuilder/adminhtml_form_element_select'),
        );

        $response = new Varien_Object();
        $response->setTypes(array());
        Mage::dispatchEvent('menubuilder_adminhtml_menu_edit_element_types', array('response'=>$response));

        foreach ($response->getTypes() as $typeName=>$typeClass) {
        	$result[$typeName] = $typeClass;
        }

        return $result;
    }
    
}