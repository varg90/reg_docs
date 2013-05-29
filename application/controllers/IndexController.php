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
            $reader = new Spreadsheet_Excel_Reader($form->firstFile->getFileName());
            echo $reader->dump(true, true);
        }
    }

}

