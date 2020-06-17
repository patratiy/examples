<?php

$_SERVER['DOCUMENT_ROOT'] = str_replace('/local/cron','',__DIR__);

include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/phpspread/vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/mailer/PHPMailer/src/Exception.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/mailer/PHPMailer/src/SMTP.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/mailer/PHPMailer/src/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$instance = new \Bethowen\Helpers\WorkerHLBlock(HLBL_RATE);

$date = $instance->selectData(array('UF_DATE', 'UF_RATE_1','UF_RATE_2','UF_RATE_3','UF_APPRAISAL'));

//$time = new DateTime('2020-05-01');

$date_correspond = [
    '01' => 'Январь',
    '02' => 'Февраль',
    '03' => 'Март',
    '04' => 'Апрель',
    '05' => 'Май',
    '06' => 'Июнь',
    '07' => 'Июль',
    '08' => 'Август',
    '09' => 'Сентябрь',
    '10' => 'Октябрь',
    '11' => 'Ноябрь',
    '12' => 'Декабрь',
];

$interval_shift = [];

for($month = 1; $month <= 12; $month++) {
    $interval = DateInterval::createFromDateString($month . ' months'); //P30D
    $time = new DateTime();
    if ($_REQUEST['run'] == 'rightnow') {
		$next = intval($time->format("m")) + 1;
		$str_d = $time->format("Y") . ((strlen($next) == 1) ? '-0' . $next : '-' . $next) . '-01';

		if ($next > 12) {
			$str_d = (intval($time->format("Y")) + 1) . '-01-01';
		}

        $time = new DateTime($str_d);
	}
    $time_shift = $time->sub($interval);

	$interval_shift[] = strtotime($time_shift->format('Y-m-d H:i:s'));
}

$massive_full_rate_month = [];

while($data_elements = $date->Fetch()) {
    if($data_elements['UF_DATE'] != null) {
        $massive_full_rate_month[] = [
            'date' => $data_elements['UF_DATE'],
            'rate_1' => $data_elements['UF_RATE_1'],
            'rate_2' => $data_elements['UF_RATE_2'],
            'rate_3' => $data_elements['UF_RATE_3'],
            'appraisal' => $data_elements['UF_APPRAISAL'],
        ];
    }
}

$massive = [];

foreach($massive_full_rate_month as $key => $value) {
    for($i = 0; $i < count($interval_shift); $i++) {
        $date_time_from = \Bitrix\Main\Type\Date::createFromTimestamp($interval_shift[$i]);

        if(!isset($massive[$date_time_from->format('m')]['rate_1'])) {
            $massive[$date_time_from->format('m')]['rate_1'] = 0;
        }
        if(!isset($massive[$date_time_from->format('m')]['rate_2'])) {
            $massive[$date_time_from->format('m')]['rate_2'] = 0;
        }
        if(!isset($massive[$date_time_from->format('m')]['rate_3'])) {
            $massive[$date_time_from->format('m')]['rate_3'] = 0;
        }
        if(!isset($massive[$date_time_from->format('m')]['appraisal'])) {
            $massive[$date_time_from->format('m')]['appraisal'] = 0;
        }

        if($i == 0) {

            if($date_time_from < $value['date']) {

                $massive[$date_time_from->format('m')]['rate_1'] += intval($value['rate_1']);
                $massive[$date_time_from->format('m')]['rate_2'] += intval($value['rate_2']);
                $massive[$date_time_from->format('m')]['rate_3'] += intval($value['rate_3']);
                $massive[$date_time_from->format('m')]['appraisal'] += intval($value['appraisal']);
                $massive[$date_time_from->format('m')]['count']++;

            }
        } else {
            $date_time_to = \Bitrix\Main\Type\Date::createFromTimestamp($interval_shift[$i - 1]);

            if($date_time_from < $value['date'] && $date_time_to >= $value['date']) {

                $massive[$date_time_from->format('m')]['rate_1'] += intval($value['rate_1']);
                $massive[$date_time_from->format('m')]['rate_2'] += intval($value['rate_2']);
                $massive[$date_time_from->format('m')]['rate_3'] += intval($value['rate_3']);
                $massive[$date_time_from->format('m')]['appraisal'] += intval($value['appraisal']);
                $massive[$date_time_from->format('m')]['count']++;

            }
        }
    }
}

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Месяц (от запуска скрипта)');
$sheet->setCellValue('B1', 'Среднее рейтинг');
$sheet->setCellValue('C1', 'Среднее по Сайту');
$sheet->setCellValue('D1', 'Среднее по КЦ');
$sheet->setCellValue('E1', 'Среднее по Курьерке');
$sheet->setCellValue('F1', 'Респондентов');

$index = 2;
foreach($massive as $key => $val) {
    if($val['count'] != 0) {
        $sheet->setCellValue('A' . $index, $date_correspond[$key]);
        $sheet->setCellValue('B' . $index, number_format( $val['appraisal'] / $val['count'] , 2 ));
        $sheet->setCellValue('C' . $index, number_format( $val['rate_1'] / $val['count'] , 2 ));
        $sheet->setCellValue('D' . $index, number_format( $val['rate_2'] / $val['count'] , 2 ));
        $sheet->setCellValue('E' . $index, number_format( $val['rate_3'] / $val['count'] , 2 ));
        $sheet->setCellValue('F' . $index, $val['count'] );
        $index++;
    }
}

$writer = new Xlsx($spreadsheet);
$writer->save($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/nps_report_' . date('Y-m-d') . '.xlsx');


$mail = new PHPMailer(true);

//Recipients
$mail->setFrom('sale@petretail.ru', 'PetRetail Roboto');

$mail->addAddress('inspenkova.av@petretail.ru', 'Испенкова А.');
$mail->addAddress('linin.an@petretail.ru', 'Линин А.');
$mail->addAddress('avlobur@gmail.com', 'Лобурь А.');
$mail->addAddress('dolinskyi.nd@petretail.ru', 'Долинский Н.');

// Attachments
$mail->addAttachment($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/nps_report_' . date('Y-m-d') . '.xlsx');

// Content
$mail->isHTML(true);
$mail->Subject = 'Ежемесячные NPS отчеты';
$mail->Body = 'Здравствуйте<br>Ежемесячный отчет NPS, во вложении.<br>----<br>Сообщение сгенерировано автоматически 1С Bitrix.';
$mail->CharSet = 'UTF-8';

$mail->send();
