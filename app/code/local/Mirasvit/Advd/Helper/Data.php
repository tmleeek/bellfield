<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Reports
 * @version   1.0.23
 * @build     737
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Advd_Helper_Data extends Mage_Core_Helper_Data
{
    public function setVariable($key, $value)
    {
        $variable = Mage::getModel('core/variable');
        $variable = $variable->loadByCode('advd_' . $key);

        $value = serialize($value);

        $variable->setPlainValue($value)
            ->setHtmlValue(Mage::getSingleton('core/date')->gmtTimestamp())
            ->setName($key)
            ->setCode('advd_' . $key)
            ->save();

        return $variable;
    }

    public function getVariable($key)
    {
        $variable = Mage::getModel('core/variable')->loadByCode('advd_' . $key);

        return unserialize($variable->getPlainValue());
    }
}
