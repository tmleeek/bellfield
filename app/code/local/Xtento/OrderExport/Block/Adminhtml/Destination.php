<?php

/**
 * Product:       Xtento_OrderExport (1.8.4)
 * ID:            z5MBfREyIoH/Qp4tKJu7lnd/g9ENXIdX9z9fcLsWfKg=
 * Packaged:      2015-07-14T18:57:47+00:00
 * Last Modified: 2013-02-10T13:10:41+01:00
 * File:          app/code/local/Xtento/OrderExport/Block/Adminhtml/Destination.php
 * Copyright:     Copyright (c) 2015 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Block_Adminhtml_Destination extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'xtento_orderexport';
        $this->_controller = 'adminhtml_destination';
        $this->_headerText = Mage::helper('xtento_orderexport')->__('Sales Export - Destinations');
        $this->_addButtonLabel = Mage::helper('xtento_orderexport')->__('Add New Destination');
        parent::__construct();
    }

    protected function _toHtml()
    {
        return $this->getLayout()->createBlock('xtento_orderexport/adminhtml_widget_menu')->toHtml() . parent::_toHtml();
    }
}