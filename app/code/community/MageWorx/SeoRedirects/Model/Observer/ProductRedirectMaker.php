<?php
/**
 * MageWorx
 * MageWorx_SeoRedirects Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoRedirects
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SeoRedirects_Model_Observer_ProductRedirectMaker extends Mage_Core_Model_Abstract
{
    /**
     *
     * @param type $observer
     * @return null
     */
    public function redirect($observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();

        if (!Mage::helper('mageworx_seoredirects')->isEnabled()) {
            return;
        }

        if ($request->getActionName() != 'noRoute') {
            return;
        }

        $storeId   = Mage::app()->getStore()->getStoreId();
        $storeCode = Mage::app()->getStore()->getCode();

        list($requestUrlRaw) = explode('?', $request->getRequestUri());

        /** @var MageWorx_SeoAll_Helper_String $stringHelper */
        $stringHelper = Mage::helper('mageworx_seoall/string');

        $requestUrl = $stringHelper->cropFirstPart($requestUrlRaw, array('/', 'index.php/', $storeCode, '/'), true);

        $redirectCollection = Mage::getModel('mageworx_seoredirects/redirect_product')->getCollection();
        $redirectCollection->addRequestPathsFilter(array($requestUrl, rtrim($requestUrl, '/') . '/'));
        $redirectCollection->addStoreFilter($storeId);

        /**
         * @var MageWorx_SeoRedirects_Model_Redirect_Product
         */
        $redirect = $redirectCollection->load()->getFirstItem();

        if (!empty($redirect['category_id'])) {
            $categoryIds = array_unique(array($redirect['category_id'], $redirect['priority_category_id']));

            $collection = Mage::getModel('catalog/category')->getCollection();
            $collection->addAttributeToFilter('entity_id', $categoryIds);
            $collection->addFieldToFilter('is_active', array('eq' => 1));
            $collection->setStoreId($storeId);

            $category = $this->_getCategoryModel($collection, $redirect);

            if ($category) {
                if ($this->_hasCustomRedirects($requestUrl, $category, $storeId)) {
                    return;
                }

                $redirect->setHits($redirect->getHits() + 1);
                $redirect->save();

                $response = Mage::app()->getResponse();
                $response->setRedirect($category->getUrl(), Mage::helper('mageworx_seoredirects')->getRedirectType());
                $response->sendResponse();
                exit;
            }
        }

        return;
    }


    /**
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @param MageWorx_SeoRedirects_Model_Redirect_Product $redirect
     * @return Mage_Catalog_Model_Category
     */
    protected function _getCategoryModel($collection, $redirect)
    {
        if ($collection->count() < 2) {
            return $collection->getFirstItem();
        }

        $isUsePriorityCategory = Mage::helper('mageworx_seoredirects')->isForceProductRedirectByPriority();

        foreach ($collection as $item) {
            if ($isUsePriorityCategory) {
                if ($redirect['priority_category_id'] == $item->getId()) {
                    return $item;
                }
            } else {
                if ($redirect['category_id'] == $item->getId()) {
                    return $item;
                }
            }
        }
    }


    /**
     * @param $requestUrl
     * @param $category
     * @param $storeId
     * @return bool
     */
    protected function _hasCustomRedirects($requestUrl, $category, $storeId)
    {
        $redirect = Mage::getModel('mageworx_seoredirects/redirect_custom')->setStoreId($storeId);

        $redirect->loadByEntity(MageWorx_SeoRedirects_Model_Redirect_Custom::CATEGORY_ENTITY_TYPE_ID, $category->getId());
        if (!$redirect->getId()) {
            $categoryUrl = Mage::getResourceSingleton('catalog/category')
                ->getAttributeRawValue($category->getId(), 'url_path', $storeId);
            $redirect->loadByEntity(MageWorx_SeoRedirects_Model_Redirect_Custom::CUSTOM_URL_ENTITY_TYPE_ID, $categoryUrl);
        }
        if ($redirect->getId() &&
            $redirect->getTargetEntityTypeId() &&
            $redirect->getTargetEntityId() &&
            $redirect->getStatus() != '0'
        ) {
            if ($redirect->getTargetEntityTypeId() == MageWorx_SeoRedirects_Model_Redirect_Custom::CUSTOM_URL_ENTITY_TYPE_ID) {
                    $url = $redirect->getTargetEntityId();
                if ($url == $requestUrl) {
                    return true;
                }
            }
        }
        return false;
    }
}