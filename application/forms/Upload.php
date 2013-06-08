<?php

class Form_Upload extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');

        $firstFile = new Zend_Form_Element_File('firstFile');
        $firstFile->setLabel('first_file')
                ->setRequired(true)
                ->addValidator('Extension', false, 'xls, xlsx');

        $secondFile = new Zend_Form_Element_File('secondFile');
        $secondFile->setLabel('second_file')
                ->setRequired(true)
                ->addValidator('Extension', false, 'xls, xlsx');

        $submit = new Zend_Form_Element_Submit('submit');

        $this->addElements(array(
            $firstFile,
            $secondFile,
            $submit,
        ));
    }

}

