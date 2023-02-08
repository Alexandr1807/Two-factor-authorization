<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

defined('BOOTSTRAP') or die('Access denied');

/** @var array $schema */
$schema['services']['yandex_delivery'] = [
    'purposes' => ['strictly_necessary'],
    'name' => 'yandex_delivery',
    'translations' => [
        'zz' => [
            'title' => 'yandex_delivery.yandex_delivery_cookie_title',
            'description' => 'yandex_delivery.yandex_delivery_cookie_description'
        ],
    ],
    'required' => true,
];

return $schema;
