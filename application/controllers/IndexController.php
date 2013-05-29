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
            $firstFileData = new Spreadsheet_Excel_Reader($form->firstFile->getFileName());
            $firstFileMembers = $this->_getMembers($firstFileData);
            
            $secondFileData = new Spreadsheet_Excel_Reader($form->secondFile->getFileName());
            $secondFileMembers = $this->_getMembers($secondFileData);
            /*
             * TODO: _someFunctionToCompareFileMembers($firstFileMembers, $secondFileMembers)
             */
        }
    }
    
    private function _getMembers($fileData)
    {
        $sheets = $fileData->sheets[0];
            $cells = $sheets['cells'];
            $members = array();
            foreach ($cells as $cell) {
                if (gettype($cell[1]) == 'integer') {
                    array_push($members, $cell);
                }
            }
            return $members;
    }

}

