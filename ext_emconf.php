<?php

/*
 * This extension was developed by Beech.it
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Mautic',
    'description'      => 'An extension to sync forms between TYPO3 and Mautic',
    'category'         => 'be',
    'version'          => '1.3.7',
    'state'            => 'beta',
    'clearcacheonload' => 0,
    'author'           => 'Woeler',
    'author_email'     => 'woeler@esoleaderboards.com',
    'author_company'   => 'Beech.it',
    'constraints'      => [
        'depends' => [
            'typo3' => '8.7.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
    'suggests' => [],
];
