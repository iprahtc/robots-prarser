<?php

class DownloadController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        if($this->request->get("file_name")) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename('error_log.xlsx'));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize('files/' . $this->request->get("file_name")));

            // читаем файл и отправляем его пользователю
            readfile('files/' . $this->request->get("file_name"));

            //Удаляем файл
            unlink('files/' . $this->request->get("file_name"));
            exit;
        }
        else
            die("<h2>Вы перешли не по ссылке скачивания</h2>");
    }

}

