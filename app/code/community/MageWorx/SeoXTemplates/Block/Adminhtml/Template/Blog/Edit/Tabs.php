<?php

/**
 * MageWorx
 * MageWorx SeoXTemplates Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoXTemplates
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoXTemplates_Block_Adminhtml_Template_Blog_Edit_Tabs extends MageWorx_SeoXTemplates_Block_Adminhtml_Template_Edit_Tabs
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _getConditionLabel()
    {
        return Mage::helper('catalog')->__('Blogs');
    }
}