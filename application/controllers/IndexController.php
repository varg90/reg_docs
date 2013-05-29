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
//            $secondFileData = new Spreadsheet_Excel_Reader($form->secondFile->getFileName());
            $sheets = $firstFileData->sheets[0];
            $cells = $sheets['cells'];
            $firstFileGrid = array();
            foreach ($cells as $cell) {
                if (gettype($cell[1]) == 'integer') {
                    array_push($firstFileGrid, $cell);
                }
            }
            throw new Exception(Zend_Debug::dump($firstFileGrid));
        }
    }

}

