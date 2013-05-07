<?php

class Form_Upload extends Twitter_Bootstrap_Form_Horizontal
{

    public function init()
    {
        $this->setMethod('post');
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');
        
        $this->addElement('file', 'file', array(
            'placeholder' => 'Upload'
        ));

        $this->addElement('button', 'submit', array(
            'label' => 'OK',
            'type' => 'submit',
            'buttonType' => 'success',
            'icon' => 'ok',
            'escape' => false
        ));
    }

}

