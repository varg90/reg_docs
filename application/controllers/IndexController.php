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

        foreach ($firstFileMembers as $firstFileMember) {
            foreach ($secondFileMembers as $secondFileMember) {
                $firstRegDate = DateTime::createFromFormat('m-d-y', $firstFileMember[11]);
                $secondRegDate = DateTime::createFromFormat('m-d-y', $secondFileMember[11]);
                $firstFullname = $firstFileMember[1];
                $secondFullname = $secondFileMember[1];
                $firstBithday = DateTime::createFromFormat('m-d-y', $firstFileMember[2]);
                $secondBithday = DateTime::createFromFormat('m-d-y', $secondFileMember[2]);

                if ($firstRegDate->format("U") > $secondRegDate->format("U")) {
                    if ($firstFullname == $secondFullname) {
                        if ($firstBithday->format("U") == $secondBithday->format("U")) {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО и ДР равны';
                            $toKSA2Data[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($firstBithday->format("d") == '01') && ($firstBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[2] = $secondFileMember[2];
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР 01.01';
                            $toKSA2Data[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($secondBithday->format("d") == '01') && ($secondBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР 01.01';
                            $toKSA2Data[] = $firstFileMember;
                            break 1;
                        } else {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР не совпадают';
                            $toKSA2Data[] = $firstFileMember;
                            $firstFileMember[13] = 'Уточнить ДР';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        }
                    } else {
                        if ($firstBithday->format("U") == $secondBithday->format("U")) {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО не совпадают, ДР равны';
                            $toKSA2Data[] = $firstFileMember;
                            $firstFileMember[13] = 'Уточнить ФИО';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($firstBithday->format("d") == '01') && ($firstBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[2] = $secondFileMember[2];
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО не совпадают, ДР 01.01';
                            $toKSA2Data[] = $firstFileMember;
                            $firstFileMember[13] = 'Уточнить ФИО';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($secondBithday->format("d") == '01') && ($secondBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО не совпадают, ДР 01.01';
                            $toKSA2Data[] = $firstFileMember;
                            $firstFileMember[13] = 'Уточнить ФИО';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'Уточнить';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        }
                    }
                } else if ($firstRegDate->format("U") < $secondRegDate->format("U")) {
                    if ($firstFullname == $secondFullname) {
                        if ($firstBithday->format("U") == $secondBithday->format("U")) {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'УЕХАЛ';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($firstBithday->format("d") == '01') && ($firstBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[2] = $secondFileMember[2];
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'УЕХАЛ. Уточнить ДР';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($secondBithday->format("d") == '01') && ($secondBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $changedOriginalData[] = $firstFileMember;
                            $firstFileMember[13] = 'УЕХАЛ. Уточнить ДР';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else {
                            
                        }
                    } else {
                        if ($firstBithday->format("U") == $secondBithday->format("U")) {
                            $firstFileMember[13] = 'Уточнить ФИО';
                            $toUFMSData[] = $firstFileMember;
                            $firstFileMember[13] = 'УЕХАЛ';
                            $changedOriginalData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($firstBithday->format("d") == '01') && ($firstBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[13] = 'Уточнить ФИО';
                            $toUFMSData[] = $firstFileMember;
                            $firstFileMember[2] = $secondFileMember[2];
                            $firstFileMember[13] = 'УЕХАЛ. Уточнить ДР';
                            $changedOriginalData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($secondBithday->format("d") == '01') && ($secondBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[13] = 'Уточнить ФИО';
                            $toUFMSData[] = $firstFileMember;
                            $firstFileMember[13] = 'УЕХАЛ. Уточнить ДР';
                            $changedOriginalData[] = $firstFileMember;
                            break 1;
                        } else {
                            $firstFileMember[13] = 'Уточнить';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        }
                    }
                } else if ($firstRegDate->format("U") == $secondRegDate->format("U")) {
                    if ($firstFullname == $secondFullname) {
                        if ($firstBithday->format("U") == $secondBithday->format("U")) {
                            $firstFileMember[13] = 'Уточнить';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($firstBithday->format("d") == '01') && ($firstBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[13] = 'Уточнить';
                            $firstFileMember[2] = $secondFileMember[2];
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($secondBithday->format("d") == '01') && ($secondBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[13] = 'Уточнить';
                            $toUFMSData[] = $firstFileMember;
                            $firstFileMember[13] = 'Исправить ДР';
                            $toKSA2Data = $firstFileMember;
                            break 1;
                        } else {
                            $firstFileMember[13] = 'Уточнить';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        }
                    } else {
                        if ($firstBithday->format("U") == $secondBithday->format("U")) {
                            $firstFileMember[13] = 'Уточнить';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($firstBithday->format("d") == '01') && ($firstBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[13] = 'Уточнить';
                            $firstFileMember[2] = $secondFileMember[2];
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        } else if (($firstBithday->format("U") != $secondBithday->format("U")) && ($secondBithday->format("d") == '01') && ($secondBithday->format("m") == '01') && ($firstBithday->format("Y") == $secondBithday->format("Y"))) {
                            $firstFileMember[13] = 'Уточнить';
                            $toUFMSData[] = $firstFileMember;
                            $firstFileMember[13] = 'Исправить ДР';
                            $toKSA2Data = $firstFileMember;
                            break 1;
                        } else {
                            $firstFileMember[13] = 'Уточнить';
                            $toUFMSData[] = $firstFileMember;
                            break 1;
                        }
                    }
                }
            }
        }
        $filesPath = realpath($this->view->baseUrl() . '/files/');

        $changedOriginalSheet->fromArray($changedOriginalData);
        $originalObjWriter = new PHPExcel_Writer_Excel5($changedOriginal);
        $changedOriginalFilePath = $filesPath . 'changed_original.xls';
        $originalObjWriter->save($changedOriginalFilePath);

        $toKSA2Sheet->fromArray($toKSA2Data);
        $KSA2ObjWriter = new PHPExcel_Writer_Excel5($toKSA2);
        $KSA2FilePath = $filesPath . 'to_ksa2.xls';
        $KSA2ObjWriter->save($KSA2FilePath);

        $toKSA2Sheet->fromArray($toKSA2Data);
        $UFMSObjWriter = new PHPExcel_Writer_Excel5($toKSA2);
        $UFMSFilePath = $filesPath . 'to_ufms.xls';
        $UFMSObjWriter->save($UFMSFilePath);

        $this->view->filesPath = $filesPath;
        $this->render('download');
    }

    public function downloadAction()
    {
        $fileName = $this->_getParam('file');
        $fileFullName = realpath($this->view->baseUrl() . '/files/') . $fileName . '.xls';
        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/octet-stream', true)
                ->setHeader('Content-Length', filesize($fileFullName))
                ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
                ->clearBody();
        $this->getResponse()
                ->sendHeaders();

        readfile($fileFullName);
    }

}

