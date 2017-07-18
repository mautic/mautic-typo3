<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mautic',
    'description' => 'An extension to sync forms between TYPO3 and Mautic',
    'category' => 'be',
    'version' => '0.0.2',
    'state' => 'alpha',
    'clearcacheonload' => 0,
    'author' => 'Jurian Janssen',
    'author_email' => 'jurian.janssen@gmail.com',
    'author_company' => 'Mautic',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'suggests' => [],
];
