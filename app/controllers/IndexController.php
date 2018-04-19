<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IndexController extends ControllerBase
{
    //массив последовательности обработки
    public $answer_array = [];

    public function initialize()
    {
        $this->tag->setTitle('Главная');
    }

    public function indexAction()
    {
        if (!empty($this->request->getPost("url"))) {
            $url_site_parse = parse_url($this->request->getPost("url"));
            if(!empty($url_site_parse['host']))
                $url = $url_site_parse['host'];
            else
                $url = $url_site_parse['path'];

            $this->view->site_parse = $url;

            $url = $url. '/robots.txt';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            $data = curl_exec($ch);
            $header_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $size_file = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            curl_close($ch);

            //Проверка наличия файла robots.txt
            $this->answer_array[1]['name'] = "Проверка наличия файла robots.txt";
            if($header_status == '200'){
                $this->buildArray(1);
            }else
                $this->buildArray(1, false);

            //Проверка указания директивы Host
            $this->answer_array[2]['name'] = "Проверка указания директивы Host";
            $parser = new RobotsTxtParser($data);
            $array_pars = $parser->getRules();
            if(!empty($array_pars['*']['host'])){
                $this->buildArray(2);
            }else
                $this->buildArray(2, false);

            //Проверка количества директив Host, прописанных в файле
            $this->answer_array[3]['name'] = "Проверка количества директив Host, прописанных в файле";
            if(substr_count(mb_strtolower($data), 'host') == 1){
                $this->buildArray(3);
            }else
                $this->buildArray(3, false);


            //Проверка размера файла robots.txt
            $this->answer_array[4]['name'] = "Проверка размера файла robots.txt";
            if($size_file <= 32000 && $header_status == '200'){
                $this->buildArray(4, true, $size_file);
            }else
                $this->buildArray(4, false, $size_file);


            //Проверка указания директивы Sitemap
            $this->answer_array[5]['name'] = "Проверка указания директивы Sitemap";
            if(!empty($array_pars['*']['sitemap'])){
                $this->buildArray(5);
            }else
                $this->buildArray(5, false);


            //Проверка кода ответа сервера для файла robots.txt
            $this->answer_array[6]['name'] = "Проверка кода ответа сервера для файла robots.txt";
            if($header_status == '200'){
                $this->buildArray(6);
            }else
                $this->buildArray(6, false, $header_status);

            $this->view->answer_array = $this->answer_array;

            //Формируем и сохраняем файл
            $this->view->file_name = $this->buildXLSX($this->answer_array);
            $this->view->site_name = $_SERVER['SERVER_NAME'];
        }
    }

    /**
     * @param data - array status
     * @return string  - url
     */
    public function buildXLSX($data){

        $extension = 'xlsx';
        $file = uniqid() . '.' . $extension;
        $path = 'files/';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //задаем значения
        $sheet->setCellValue('A1', '№');
        $sheet->setCellValue('B1', 'Название проекта');
        $sheet->setCellValue('C1', 'Статус');
        $sheet->setCellValue('E1', 'Текущее состояние');

        //Задаем цвет
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('A2C4C9');
        $sheet->getStyle('A2:E2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('EFEFEF');

        //Жирный текст
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        //Выравнивание текста
        $sheet->getStyle('A1:A50')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:A50')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('C1:C50')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C1:C50')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B1:B50')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('D1:D50')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('E1:E50')->getAlignment()->setWrapText(true);
        $sheet->getStyle('B1:B50')->getAlignment()->setWrapText(true);

        //Ширина
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(53);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(11);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(65);

        $iteration = 3;

        foreach ($data as $i=>$v) {
            //Заполнение полей
            $sheet->setCellValue('A'.$iteration, $i);
            $sheet->setCellValue('B'.$iteration, $v['name']);
            $sheet->setCellValue('C' . $iteration, $v['status']);
            if($v['status'] == 'ok') {
                //Цвет для статуса ОК
                $spreadsheet->getActiveSheet()->getStyle('C'. $iteration)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('93C47D');
            }else{
                //Цвет для статуса error
                $spreadsheet->getActiveSheet()->getStyle('C'. $iteration)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('E06666');
            }

            $sheet->setCellValue('D'. $iteration, 'Состояние');
            $sheet->setCellValue('D'. ($iteration + 1), 'Рекомендации');
            $sheet->setCellValue('E'. $iteration, $v['situation']);
            $sheet->setCellValue('E'. ($iteration + 1), $v['recommendation']);


            //Объединение колонок
            $spreadsheet->getActiveSheet()->mergeCells('A'.$iteration.':A'.($iteration + 1));
            $spreadsheet->getActiveSheet()->mergeCells('B'.$iteration.':B'.($iteration + 1));
            $spreadsheet->getActiveSheet()->mergeCells('C'.$iteration.':C'.($iteration + 1));

            //Задаем цвет разделительного блока
            $sheet->getStyle('A'.($iteration + 2).':E'.($iteration + 2))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('EFEFEF');
            $iteration += 3;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path.$file);

        return $file;
    }

    /**
     * @param int number
     * @param boolean flag
     * @param mix data
     * @return void
     */
    public function buildArray($numbr, $flag = true, $data = null){
        switch ($numbr){
            case 1:
                if($flag) {
                    $this->answer_array[1]['status'] = 'ok';
                    $this->answer_array[1]['situation'] = 'Файл robots.txt присутствует';
                    $this->answer_array[1]['recommendation'] = 'Доработки не требуются';
                }else{
                    $this->answer_array[1]['status'] = 'error';
                    $this->answer_array[1]['situation'] = 'Файл robots.txt отсутствует';
                    $this->answer_array[1]['recommendation'] = 'Программист: Создать файл robots.txt и разместить его на сайте.';
                }
                break;
            case 2:
                if($flag){
                    $this->answer_array[2]['status'] = 'ok';
                    $this->answer_array[2]['situation'] = 'Директива Host указана';
                    $this->answer_array[2]['recommendation'] = 'Доработки не требуются';
                }else{
                    $this->answer_array[2]['status'] = 'error';
                    $this->answer_array[2]['situation'] = 'В файле robots.txt не указана директива Host';
                    $this->answer_array[2]['recommendation'] = 'Программист: Для того, чтобы поисковые системы знали, какая версия сайта является основных зеркалом, необходимо прописать адрес основного зеркала в директиве Host. В данный момент это не прописано. Необходимо добавить в файл robots.txt директиву Host. Директива Host задётся в файле 1 раз, после всех правил.';
                }
                break;
            case 3:
                if($flag){
                    $this->answer_array[3]['status'] = 'ok';
                    $this->answer_array[3]['situation'] = 'В файле прописана 1 директива Host';
                    $this->answer_array[3]['recommendation'] = 'Доработки не требуются';
                }else{
                    $this->answer_array[3]['status'] = 'error';
                    $this->answer_array[3]['situation'] = 'В файле прописано несколько директив Host';
                    $this->answer_array[3]['recommendation'] = 'Программист: Директива Host должна быть указана в файле толоко 1 раз. Необходимо удалить все дополнительные директивы Host и оставить только 1, корректную и соответствующую основному зеркалу сайта';
                }
                break;
            case 4:
                if($flag){
                    $this->answer_array[4]['status'] = 'ok';
                    $this->answer_array[4]['situation'] = 'Размер файла robots.txt составляет ' . $data . ' байт, что находится в пределах допустимой нормы';
                    $this->answer_array[4]['recommendation'] = 'Доработки не требуются';
                }else{
                    $this->answer_array[4]['status'] = 'error';
                    $this->answer_array[4]['situation'] = 'Размера файла robots.txt составляет ' . $data . ' байт, что превышает допустимую норму';
                    $this->answer_array[4]['recommendation'] = 'Программист: Максимально допустимый размер файла robots.txt составляем 32 кб. Необходимо отредактировть файл robots.txt таким образом, чтобы его размер не превышал 32 Кб';
                }
                break;
            case 5:
                if($flag){
                    $this->answer_array[5]['status'] = 'ok';
                    $this->answer_array[5]['situation'] = 'Директива Sitemap указана';
                    $this->answer_array[5]['recommendation'] = 'Доработки не требуются';
                }else {
                    $this->answer_array[5]['status'] = 'error';
                    $this->answer_array[5]['situation'] = 'В файле robots.txt не указана директива Sitemap';
                    $this->answer_array[5]['recommendation'] = 'Программист: Добавить в файл robots.txt директиву Sitemap';
                }
                break;
            case 6:
                if($flag){
                    $this->answer_array[6]['status'] = 'ok';
                    $this->answer_array[6]['situation'] = 'Файл robots.txt отдаёт код ответа сервера 200';
                    $this->answer_array[6]['recommendation'] = 'Доработки не требуются';
                }else {
                    $this->answer_array[6]['status'] = 'error';
                    $this->answer_array[6]['situation'] = 'При обращении к файлу robots.txt сервер возвращает код ответа (' . $data . ')';
                    $this->answer_array[6]['recommendation'] = 'Программист: Файл robots.txt должны отдавать код ответа 200, иначе файл не будет обрабатываться. Необходимо настроить сайт таким образом, чтобы при обращении к файлу robots.txt сервер возвращает код ответа 200';
                }
                break;
        }
    }

}

