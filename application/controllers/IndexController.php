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
            if (!($form->firstFile->receive()) || !($form->secondFile->receive())) {
                throw new Zend_Http_Exception(Zend_Debug::dump('File uploading error'));
            }
            $firstFileData = PHPExcel_IOFactory::load($form->firstFile->getFileName());
            $secondFileData = PHPExcel_IOFactory::load($form->secondFile->getFileName());
            
            $firstFileMembers = $this->_getMembers($firstFileData);
            $secondFileMembers = $this->_getMembers($secondFileData);
            
            foreach ($secondFileMembers as $secondFileMember) {
                foreach ($firstFileMembers as $firstFileMember) {
                    $this->_compareRecords($firstFileMember, $secondFileMember);
                }
            }
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
    
    private function _compareRecords($firstRecord, $secondRecord)
    {
        $firstRegDate = new DateTime($firstRecord[11]);
        $secondRegDate = new DateTime($secondRecord[11]);
        $firstFullname = $firstRecord[1];
        $secondFullname = $secondRecord[1];
        $firstBithday = new DateTime($firstRecord[2]);
        $secondBithday = new DateTime($secondRecord[2]);
        
        if ($firstRegDate->format("U") > $secondRegDate->format("U")) {
            if($firstFullname == $secondFullname) {
                if ($firstBithday->format("U") == $secondBithday->format("U")) {
                    
                }
            } else {
                
            }
        } else if ($firstRegDate > $secondRegDate) {
            if($firstFullname == $secondFullname) {
                
            } else {
                
            }
        } else if ($firstRegDate == $secondRegDate) {
            if($firstFullname == $secondFullname) {
                
            } else {
                
            }
        }
    }

    
    //$start_date = date("Y-m-d H:i:s", strtotime("12/16/2012 02:53"));
}

