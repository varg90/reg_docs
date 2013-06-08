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

            $this->_compareMembers($firstFileMembers, $secondFileMembers);
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

    private function _compareMembers($firstFileMembers, $secondFileMembers)
    {
        $changedOriginal = new PHPExcel();
        $changedOriginal->setActiveSheetIndex(0);
        $changedOriginalSheet = $changedOriginal->getActiveSheet();
        $changedOriginalSheet->getColumnDimension('b')->setWidth('30');
        $changedOriginalData = array();
        
        $toKSA2 = new PHPExcel();
        $toKSA2->setActiveSheetIndex(0);
        $toKSA2Sheet = $toKSA2->getActiveSheet();
        $toKSA2Data = array();
        
        $toUFMS = new PHPExcel();
        $toUFMS->setActiveSheetIndex(0);
        $toUFMSSheet = $toUFMS->getActiveSheet();
        $toUFMSData = array();
        
        foreach ($secondFileMembers as $secondFileMember) {
            foreach ($firstFileMembers as $firstFileMember) {
                $firstRegDate = DateTime::createFromFormat('m-d-y',$firstFileMember[11]);
                $secondRegDate = DateTime::createFromFormat('m-d-y',$secondFileMember[11]);
                $firstFullname = $firstFileMember[1];
                $secondFullname = $secondFileMember[1];
                $firstBithday = DateTime::createFromFormat('m-d-y',$firstFileMember[2]);
                $secondBithday = DateTime::createFromFormat('m-d-y',$secondFileMember[2]);

                if ($firstRegDate->format("U") > $secondRegDate->format("U")) {
                    if ($firstFullname == $secondFullname) {
                        if ($firstBithday->format("U") == $secondBithday->format("U")) {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО и ДР равны';
                            $toKSA2Data[] = $firstFileMember;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U"))
                                && ($firstBithday->format("d") == '01')
                                && ($firstBithday->format("m") == '01')
                                && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[2] = $secondFileMember[2];
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР 01.01';
                            $toKSA2Data[] = $firstFileMember;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U"))
                                && ($secondBithday->format("d") == '01')
                                && ($secondBithday->format("m") == '01')
                                && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР 01.01';
                            $toKSA2Data[] = $firstFileMember;
                        } else {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР не совпадают';
                            $toKSA2Data[] = $firstFileMember;
                            
                        }
                    } else {
                        
                    }
                } else if ($firstRegDate > $secondRegDate) {
                    if ($firstFullname == $secondFullname) {
                        
                    } else {
                        
                    }
                } else if ($firstRegDate == $secondRegDate) {
                    if ($firstFullname == $secondFullname) {
                        
                    } else {
                        
                    }
                }
            }
        }
        $changedOriginalSheet->fromArray($changedOriginalData);
        $objWriter = new PHPExcel_Writer_Excel5($changedOriginal);
        $objWriter->save('C:\xampp\tmp\123.xls');
    }

    //$start_date = date("Y-m-d H:i:s", strtotime("12/16/2012 02:53"));
}

