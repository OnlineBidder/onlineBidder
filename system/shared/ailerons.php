<?php
/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 25.11.14
 * Time: 1:02
 */

namespace Satan\shared\Ailerons;


/**
 * Элероны
 * @package Satan\shared
 */
const PLATFORM = 1;     // платформа: vk, facebook, yandex.direct, ...
const AD_TITLE = 2;     // заголовок объявления
const AD_IMAGE = 3;     // картинка объявления
const AD_TEXT  = 4;     // текст объявления
const AGE      = 5;     // возраст
const SEX      = 6;     // пол
const PRICE    = 7;     // цена

// Типы элеронов:
const TYPE          = 1000;
const TYPE_SET      = 1001;     // набор заведомо ограниченного множества предустановленных значений
const TYPE_DISCRETE = 1002;     // дискретные значения

const ADMITTED_REGION = 2000;   // область допустимых значений
const DISCRETE_LEVEL  = 2001;   // шаг дискретности

// Платформы
const PLATFORM_VK       = 3000;
const PLATFORM_FACEBOOK = 3001;
const PLATFORM_YANDEX   = 3002;
const PLATFORM_VIRTUAL  = 3003;     // тестовый стэнд

// Пол
const SEX_MALE   = 4000;        // мужской
const SEX_FEMALE = 4001;        // женский
const SEX_OTHER  = 4002;        // неопределен

function getDefinition()
{
    return [
        PLATFORM => [
            TYPE            => TYPE_SET,
            ADMITTED_REGION => [PLATFORM_VK, PLATFORM_FACEBOOK, PLATFORM_YANDEX, PLATFORM_VIRTUAL],
        ],
        AD_TITLE => [
            TYPE => TYPE_SET,
        ],
        AD_IMAGE => [
            TYPE => TYPE_SET,
        ],
        AD_TEXT => [
            TYPE => TYPE_SET,
        ],
        AGE => [
            TYPE            => TYPE_DISCRETE,
            ADMITTED_REGION => [1, 80],
            DISCRETE_LEVEL  => 1,
        ],
        SEX => [
            TYPE            => TYPE_SET,
            ADMITTED_REGION => [SEX_MALE, SEX_FEMALE, SEX_OTHER],
        ],
        PRICE => [
            TYPE           => TYPE_DISCRETE,
            DISCRETE_LEVEL => 1,
        ]
    ];
}