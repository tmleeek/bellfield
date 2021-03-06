<?php

/* * ****************************************************
 * Package   : Brand
 * Author    : ArexWorks
 * Copyright : (c) 2015
 * ***************************************************** */
?>
<?php

class ARW_Brand_Adminhtml_BrandController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->_title($this->__('ArexWorks'))
            ->_title($this->__('Brand'))
            ->_title($this->__('Manage Brands'));
        $this->loadLayout()
            ->_setActiveMenu('arexworks/brand/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('ArexWorks'), Mage::helper('adminhtml')->__('ArexWorks'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Brands'), Mage::helper('adminhtml')->__('Manage Brands'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('brand/brand')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('brand_data', $model);

            $this->_initAction();

            $this->_title($id ? $model->getTitle() : $this->__('New Brand'));

            $item = $id ? Mage::helper('catalog')->__('Edit Brand') : Mage::helper('catalog')->__('New Brand');

            $this->_addBreadcrumb($item, $item);

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('brand/adminhtml_brand_edit'))
                ->_addLeft($this->getLayout()->createBlock('brand/adminhtml_brand_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('brand')->__('Item does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if (isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('icon');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode 
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders 
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'arw' . DS . 'brand' . DS;
                    $uploader->save($path, $_FILES['icon']['name']);
                } catch (Exception $e) {

                }

                //this way the name is saved in DB
                $data['icon'] = 'arw/brand/' . str_replace(' ', '_', $_FILES['icon']['name']);
            } else {
                if (isset($data['icon']['delete']) && $data['icon']['delete'] == 1) {
                    $data['icon'] = '';
                } else {
                    unset($data['icon']);
                }
            }
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('image');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode 
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders 
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'arw' . DS . 'brand' . DS;
                    $uploader->save($path, $_FILES['image']['name']);
                } catch (Exception $e) {

                }

                //this way the name is saved in DB
                $data['image'] = 'arw/brand/' . str_replace(' ', '_', $_FILES['image']['name']);
            } else {
                if (isset($data['image']['delete']) && $data['image']['delete'] == 1) {
                    $data['image'] = '';
                } else {
                    unset($data['image']);
                }
            }

            if (!isset($data['url_key']) || $data['url_key'] == '') {
                $data['url_key'] = Mage::getModel('catalog/product_url')->formatUrlKey($data['title']);
            }
            $data['status'] = $data['brand_status'];

            $model = Mage::getModel('brand/brand');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                if (!Mage::helper('brand')->getAttributeOptionValue('arw_brand', $model->getTitle())) {
                    Mage::helper('brand')->addAttributeOption('arw_brand', $model->getTitle());
                }
                $urlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath('brand/view/' . $model->getId())
                    ->setIdPath('brand/view/' . $model->getId());
                if (Mage::helper('brand')->urlKey() != '') {
                    $urlRewrite->setRequestPath(Mage::helper('brand')->urlKey() . '/' . $model->getUrlKey());
                } else {
                    $urlRewrite->setRequestPath('brand/' . $model->getUrlKey());
                }
                $urlRewrite->setTargetPath('brand/index/view/id/' . $model->getId());
                $urlRewrite->setIsSystem(1);
                $urlRewrite->save();
                if (isset($data['brand']['product_ids']) && ($data['brand']['product_ids'] != '' || $data['brand']['product_ids'] != null)) {
                    $decode = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['brand']['product_ids']);
                    $productIds = array();
                    foreach ($decode as $key => $value) {
                        $productIds[] = (int)$key;
                    }
                    $products = Mage::getModel('brand/product')->getCollection()
                        ->addFieldToFilter('brand_id', array('eq' => $model->getId()));
                    $productIdsInBrand = array();
                    foreach ($products as $product) {
                        $productIdsInBrand[] = (int)$product->getProductId();
                    }
                    $productIdsDelete = array_diff($productIdsInBrand, $productIds);
                    $productIdsInsert = array_diff($productIds, $productIdsInBrand);
                    $productsDelete = Mage::getModel('brand/product')->getCollection()
                        ->addFieldToFilter('product_id', array('in' => $productIdsDelete))
                        ->addFieldToFilter('brand_id', array('eq' => $model->getId()));
                    foreach ($productsDelete as $product) {
                        $productModel = Mage::getModel('catalog/product')->load($product->getProductId());
                        $productModel->setData('arw_brand', '');
                        $productModel->save();
                        $product->delete();
                    }
                    foreach ($productIdsInsert as $id) {
                        $p = Mage::getModel('brand/product');
                        $p->setBrandId($model->getId());
                        $p->setProductId($id);
                        $p->save();
                        $productModel = Mage::getModel('catalog/product')->load($id);
                        $productModel->setData('arw_brand', Mage::helper('brand')->getAttributeOptionValue('arw_brand', $model->getTitle()));
                        $productModel->save();
                    }
                } else {
                    if (isset($data['brand']['product_ids']) && ($data['brand']['product_ids'] == '' || $data['brand']['product_ids'] == null)) {
                        $products = Mage::getModel('brand/product')->getCollection()
                            ->addFieldToFilter('brand_id', array('eq' => $model->getId()));
                        foreach ($products as $product) {
                            $productModel = Mage::getModel('catalog/product')->load($product->getProductId());
                            $productModel->setData('arw_brand', '');
                            $productModel->save();
                            $product->delete();
                        }
                    }
                }
                $products = Mage::getModel('brand/product')->getCollection()
                    ->addFieldToFilter('brand_id', array('eq' => $model->getId()));
                $model->setData('number_of_products', (int)count($products));
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('brand')->__('Item was successfully saved.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('brand')->__('Unable to find item to save.'));
        $this->_redirect('*/*/');
    }

    public function refreshUrlAction()
    {
        try {
            $urlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath('brand/index')
                ->setIdPath('brand/index');
            if (Mage::helper('brand')->urlKey() != '') {
                $urlRewrite->setRequestPath(Mage::helper('brand')->urlKey());
            } else {
                $urlRewrite->setRequestPath('brand');
            }
            $urlRewrite->setTargetPath('brand/index/index');
            $urlRewrite->setIsSystem(1);
            $urlRewrite->save();
            $collection = Mage::getModel('brand/brand')->getCollection();
            foreach ($collection as $brand) {
                $urlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath('brand/view/' . $brand->getId())
                    ->setIdPath('brand/view/' . $brand->getId());
                if (Mage::helper('brand')->urlKey() != '') {
                    $urlRewrite->setRequestPath(Mage::helper('brand')->urlKey() . '/' . $brand->getUrlKey());
                } else {
                    $urlRewrite->setRequestPath('brand/' . $brand->getUrlKey());
                }
                $urlRewrite->setTargetPath('brand/index/view/id/' . $brand->getId());
                $urlRewrite->setIsSystem(1);
                $urlRewrite->save();
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('brand')->__('All urls were successfully rewrited.'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('brand/brand');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $productCollection = Mage::getModel('brand/product')->getCollection()
                    ->addFieldToFilter('brand_id', array('eq' => $this->getRequest()->getParam('id')));
                foreach ($productCollection as $product) {
                    $product->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $brandIds = $this->getRequest()->getParam('brand');
        if (!is_array($brandIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($brandIds as $brandId) {
                    $brand = Mage::getModel('brand/brand')->load($brandId);
                    $productCollection = Mage::getModel('brand/product')->getCollection()
                        ->addFieldToFilter('brand_id', array('eq' => $brand->getId()));
                    foreach ($productCollection as $product) {
                        $product->delete();
                    }
                    $brand->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted.', count($brandIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $brandIds = $this->getRequest()->getParam('brand');
        if (!is_array($brandIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($brandIds as $brandId) {
                    $brand = Mage::getSingleton('brand/brand')
                        ->load($brandId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated.', count($brandIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function productAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('brand.edit.tab.product')
            ->setProductIds($this->getRequest()->getPost('product_ids', null));
        $this->renderLayout();
    }

    public function productGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('brand.edit.tab.product')
            ->setProductIds($this->getRequest()->getPost('product_ids', null));
        $this->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'brand.csv';
        $content = $this->getLayout()->createBlock('brand/adminhtml_brand_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'brand.xml';
        $content = $this->getLayout()->createBlock('brand/adminhtml_brand_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}
