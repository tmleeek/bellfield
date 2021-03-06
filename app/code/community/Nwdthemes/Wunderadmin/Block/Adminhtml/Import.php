<?php

/**
 * Nwdthemes Wunder Admin Extension
 *
 * @package     Wunderadmin
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Wunderadmin_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     * Constructor
     */

    public function __construct() {

        parent::__construct();

        $this->_blockGroup = 'wunderadmin';
        $this->_controller = 'adminhtml_import';
		$this->_headerText = Mage::helper('wunderadmin')->__('Wunderadmin import color scheme.');

		$this->_updateButton('save', 'label', Mage::helper('wunderadmin')->__('Import Color Scheme'));
		$this->_updateButton('save', 'onclick', 'importForm.submit();');
		$this->_removeButton('back');
		$this->_removeButton('reset');

		$buttonData = array(
			'label' 	=>  Mage::helper('wunderadmin')->__('Get More Color Schemes'),
			'onclick'	=> 	"popWin('" . Mage::helper('wunderadmin')->getColorSchemesUrl() . "', '_blank')",
			'class'     =>  'get_more'
		);
		$this->_addButton('get_more_color_schemes', $buttonData, 0, 300, 'header');
    }
}