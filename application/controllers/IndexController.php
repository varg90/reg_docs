<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $form = new Form_Upload;
        $this->view->form = $form;
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

            $firstFileData = PHPExcel_IOFactory::load($form->firstFile->getFileName());
            $firstFileMembers = $this->_getMembers($firstFileData);

            $secondFileData = PHPExcel_IOFactory::load($form->secondFile->getFileName());
            $secondFileMembers = $this->_getMembers($secondFileData);
            /*
             * TODO: _someFunctionToCompareFileMembers($firstFileMembers, $secondFileMembers)
             */
        }
    }

    private function _getMembers(PHPExcel $fileData)
    {
        $rows = $fileData->getActiveSheet()->toArray();
        $members = array();
        foreach ($rows as $row) {
            if (is_numeric($row[0])) {
                    array_push($members, $row);
            }
        }
        return $members;
    }

}

