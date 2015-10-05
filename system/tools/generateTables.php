<?php

require_once '../shared/ailerons.php';

use Satan\shared\Ailerons;

// Имена для колонок таблицы
$map = [
    Ailerons\PLATFORM => 'platform',
    Ailerons\AD_TITLE => 'ad_title',
    Ailerons\AD_IMAGE => 'ad_image',
    Ailerons\AD_TEXT  => 'ad_text',
    Ailerons\AGE      => 'age',
    Ailerons\SEX      => 'sex',
    Ailerons\PRICE    => 'price',
];

$columns = [];
foreach (Ailerons\getDefinition() as $aileronId => $aileron)
{
    if (isset($map[$aileronId])) {
        $columnName = '`' . $map[$aileronId] . '`';
    } else {
        $columnName = '`aileron_' . $aileronId . '`';
    }

    switch ($aileron[Ailerons\TYPE]) {
        case Ailerons\TYPE_SET:
            if (isset($aileron[Ailerons\ADMITTED_REGION])) {
                $columnType = "ENUM('" . implode("', '", $aileron[Ailerons\ADMITTED_REGION]) . "') NOT NULL";
            } else {
                $columnType = "MEDIUMINT UNSIGNED NOT NULL";
            }
            break;

        case Ailerons\TYPE_DISCRETE:
            $columnType = 'INT UNSIGNED NOT NULL';
            if (isset($aileron[Ailerons\ADMITTED_REGION])) {
                if ($aileron[Ailerons\ADMITTED_REGION][1] <= 255) {
                    $columnType = 'TINYINT UNSIGNED NOT NULL';
                } elseif ($aileron[Ailerons\ADMITTED_REGION][1] <= 65535) {
                    $columnType = 'SMALLINT UNSIGNED NOT NULL';
                } elseif ($aileron[Ailerons\ADMITTED_REGION][1] <= 16777215) {
                    $columnType = 'MEDIUMINT UNSIGNED NOT NULL';
                }
            }
            break;

        default:
            throw new Exception();
            break;
    }

    $columns[] = $columnName . ' ' . $columnType;
}

$sql = 'CREATE TABLE `satan_datamining_raw_log` (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    campaign_id MEDIUMINT UNSIGNED NOT NULL,
    ' . implode(",\n    ", $columns) . ',
    PRIMARY KEY(id),
    INDEX `datamining_raw_log_campaign_id` (`campaign_id`)
) ENGINE=InnoDB;';

echo $sql;
