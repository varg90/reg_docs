<?php

class IndexController extends Zend_Controller_Action
{

    protected $_toKSA2Data = array();
    protected $_changedOriginalData = array();
    protected $_toUFMSData = array();

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
            $this->_saveNewFiles();
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
        foreach ($firstFileMembers as $firstFileMember) {
            foreach ($secondFileMembers as $secondFileMember) {
                if ($firstFileMember[8] == $secondFileMember[8]) {
                    if (DateTime::createFromFormat('m-d-y', $firstFileMember[11])->format("U")
                            > DateTime::createFromFormat('m-d-y',
                                    $secondFileMember[11])->format("U")) {
                        $this->_firstDateGreaterThanSecond($firstFileMember,
                                $secondFileMember);
                    } else if (DateTime::createFromFormat('m-d-y',
                                    $firstFileMember[11])->format("U") == DateTime::createFromFormat('m-d-y',
                                    $secondFileMember[11])->format("U")) {
                        $this->_datesEqual($firstFileMember, $secondFileMember);
                    } else if
                    (DateTime::createFromFormat('m-d-y', $firstFileMember[11])->format("U")
                            < DateTime::createFromFormat('m-d-y',
                                    $secondFileMember[11])->format("U")) {
                        $this->_firstDateLessThanSecond($firstFileMember,
                                $secondFileMember);
                    }
                }
            }
        }
    }

    private function _firstDateGreaterThanSecond($firstFileMember,
            $secondFileMember)
    {
        if ($firstFileMember[1] == $secondFileMember[1]) {
            if (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    == DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U")) {
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО и ДР равны';
                $this->_toKSA2Data[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[2] = $secondFileMember[2];
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР 01.01';
                $this->_toKSA2Data[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР 01.01';
                $this->_toKSA2Data[] = $firstFileMember;
            } else {
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО равны, ДР не совпадают';
                $this->_toKSA2Data[] = $firstFileMember;
                $firstFileMember[13] = 'Уточнить ДР';
                $this->_toUFMSData[] = $firstFileMember;
            }
        } else {
            if (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    == DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U")) {
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО не совпадают, ДР равны';
                $this->_toKSA2Data[] = $firstFileMember;
                $firstFileMember[13] = 'Уточнить ФИО';
                $this->_toUFMSData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[2] = $secondFileMember[2];
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО не совпадают, ДР 01.01';
                $this->_toKSA2Data[] = $firstFileMember;
                $firstFileMember[13] = 'Уточнить ФИО';
                $this->_toUFMSData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'Удалить. Дата 1 > Дата 2, ФИО не совпадают, ДР 01.01';
                $this->_toKSA2Data[] = $firstFileMember;
                $firstFileMember[13] = 'Уточнить ФИО';
                $this->_toUFMSData[] = $firstFileMember;
            } else {
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'Уточнить';
                $this->_toUFMSData[] = $firstFileMember;
            }
        }
    }

    private function _datesEqual($firstFileMember, $secondFileMember)
    {
        if ($firstFileMember[1] == $secondFileMember[1]) {
            if (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    == DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U")) {
                $firstFileMember[13] = 'Уточнить';
                $this->_toUFMSData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[13] = 'Уточнить';
                $firstFileMember[2] = $secondFileMember[2];
                $this->_toUFMSData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[13] = 'Уточнить';
                $this->_toUFMSData[] = $firstFileMember;
                $firstFileMember[13] = 'Исправить ДР';
                $this->_toKSA2Data = $firstFileMember;
            } else {
                $firstFileMember[13] = 'Уточнить';
                $this->_toUFMSData[] = $firstFileMember;
            }
        } else {
            if (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    == DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U")) {
                $firstFileMember[13] = 'Уточнить';
                $this->_toUFMSData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[13] = 'Уточнить';
                $firstFileMember[2] = $secondFileMember[2];
                $this->_toUFMSData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[13] = 'Уточнить';
                $this->_toUFMSData[] = $firstFileMember;
                $firstFileMember[13] = 'Исправить ДР';
                $this->_toKSA2Data = $firstFileMember;
            } else {
                $firstFileMember[13] = 'Уточнить';
                $this->_toUFMSData[] = $firstFileMember;
            }
        }
    }

    private function _firstDateLessThanSecond($firstFileMember,
            $secondFileMember)
    {
        if ($firstFileMember[1] == $secondFileMember[1]) {
            if (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    == DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U")) {
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'УЕХАЛ';
                $this->_toUFMSData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[2] = $secondFileMember[2];
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'УЕХАЛ. Уточнить ДР';
                $this->_toUFMSData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $this->_changedOriginalData[] = $firstFileMember;
                $firstFileMember[13] = 'УЕХАЛ. Уточнить ДР';
                $this->_toUFMSData[] = $firstFileMember;
            } else {
                
            }
        } else {
            if (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    == DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U")) {
                $firstFileMember[13] = 'Уточнить ФИО';
                $this->_toUFMSData[] = $firstFileMember;
                $firstFileMember[13] = 'УЕХАЛ';
                $this->_changedOriginalData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[13] = 'Уточнить ФИО';
                $this->_toUFMSData[] = $firstFileMember;
                $firstFileMember[2] = $secondFileMember[2];
                $firstFileMember[13] = 'УЕХАЛ. Уточнить ДР';
                $this->_changedOriginalData[] = $firstFileMember;
            } else if ((DateTime::createFromFormat('m-d-y', $firstFileMember[2])->format("U")
                    != DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("U"))
                    && (DateTime::createFromFormat('m-d-y', $secondFileMember[2])->format("d")
                    == '01') && (DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("m") == '01') && (DateTime::createFromFormat('m-d-y',
                            $firstFileMember[2])->format("Y") == DateTime::createFromFormat('m-d-y',
                            $secondFileMember[2])->format("Y"))) {
                $firstFileMember[13] = 'Уточнить ФИО';
                $this->_toUFMSData[] = $firstFileMember;
                $firstFileMember[13] = 'УЕХАЛ. Уточнить ДР';
                $this->_changedOriginalData[] = $firstFileMember;
            } else {
                $firstFileMember[13] = 'Уточнить';
                $this->_toUFMSData[] = $firstFileMember;
            }
        }
    }

    private function _saveNewFiles()
    {
        $changedOriginal = new PHPExcel();
        $changedOriginal->setActiveSheetIndex(0);
        $changedOriginalSheet = $changedOriginal->getActiveSheet();
        $changedOriginalSheet->getColumnDimension('b')->setWidth('30');

        $toKSA2 = new PHPExcel();
        $toKSA2->setActiveSheetIndex(0);
        $toKSA2Sheet = $toKSA2->getActiveSheet();

        $toUFMS = new PHPExcel();
        $toUFMS->setActiveSheetIndex(0);
        $toUFMSSheet = $toUFMS->getActiveSheet();

        $filesPath = realpath($this->view->baseUrl() . '/files/');

        $changedOriginalSheet->fromArray($this->_changedOriginalData);
        $originalObjWriter = new PHPExcel_Writer_Excel5($changedOriginal);
        $changedOriginalFilePath = $filesPath . 'changed_original.xls';
        $originalObjWriter->save($changedOriginalFilePath);

        $toKSA2Sheet->fromArray($this->_toKSA2Data);
        $KSA2ObjWriter = new PHPExcel_Writer_Excel5($toKSA2);
        $KSA2FilePath = $filesPath . 'to_ksa2.xls';
        $KSA2ObjWriter->save($KSA2FilePath);

        $toUFMSSheet->fromArray($this->_toUFMSData);
        $UFMSObjWriter = new PHPExcel_Writer_Excel5($toUFMS);
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
                ->setHeader('Cache-Control',
                        'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/octet-stream', true)
                ->setHeader('Content-Length', filesize($fileFullName))
                ->setHeader('Content-Disposition',
                        'attachment; filename=' . $fileName)
                ->clearBody();
        $this->getResponse()
                ->sendHeaders();

        readfile($fileFullName);
    }

}

