<?php
/**
 * TSDesigns_MenuBuilder_Block_Adminhtml_Form_Element_Colorpicker
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
class TSDesigns_MenuBuilder_Block_Adminhtml_Form_Element_Colorpicker extends Varien_Data_Form_Element_Text
{
    /**
     * @var Hex color
     */
    protected $_value;
    
	public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('text');
        $this->setExtType('textfield');
        if (isset($attributes['value'])) {
            $this->setValue($attributes['value']);
        }
    }
    
    public function getElementHtml()
    {
        $this->addClass('input-text');

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s style="width:110px;" />'
            . ' &nbsp; <button alt="" id="%s_swatch" title="%s" style="background:none; border:0px; width:12px;  height:12px; font-size:8px;" %s>%s</button>',
            $this->getName(), $this->getHtmlId(), $this->_escape($this->getValue()), $this->serialize($this->getHtmlAttributes()),
            $this->getHtmlId(), __('Select Color'), ($this->getDisabled() ? 'disabled="true"' : ''), '&nbsp;' //'<img src="/skin/adminhtml/default/default/images/menubuilder/icon_pipette.gif" />'
        );

        $html .= sprintf('
            <script type="text/javascript">
            //<![CDATA[
                new Control.ColorPicker("%s", { IMAGE_BASE: "%s", "swatch" : "%s" });
            //]]>
            </script>',
            $this->getHtmlId(), 
            Mage::getBaseUrl('js') . '/colorpicker/img/',
            $this->getHtmlId() . '_swatch'
        );

        $html .= $this->getAfterElementHtml();

        return $html;
    	
    }
    /*
    public function getAfterElementHtml()
    {
    	$html = '';
    	if(parent::getAfterElementHtml()) {
    		$html.= parent::getAfterElementHtml();
    	}
    	
		$html.= '<script type="text/javascript">';
		$html.= "  new Control.ColorPicker('" . $this->getHtmlId() . "', { IMAGE_BASE : '" . Mage::getBaseUrl('js') . "/colorpicker/img/', 'swatch' : 'colorbox4' });";
		$html.= '</script>';
		
		return $html;
    }
    */
}
