<?php
declare (strict_types = 1);

namespace BeechIt\Mautic\ViewHelpers\Form;

/*
* This source file is proprietary property of Beech.it
* Date: 13-4-17
* All code (c) Beech.it all rights reserved
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*
* @license   http://www.opensource.org/licenses/mit-license.html  MIT License
*/

use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;
use BeechIt\Mautic\Service\MauticService;

/**
 * Class MauticPropertiesViewHelper
 */
class MauticPropertiesViewHelper extends SelectViewHelper
{
    private $mauticService;

    /**
     * MauticPropertiesViewHelper constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->mauticService = new MauticService();
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        $options[''] = 'None';

        $api = $this->mauticService->createMauticApi('contactFields');

        $personFields = $api->getList();

        foreach ($personFields['fields'] as $field) {

            $options[$field['alias']] = $field['label'];

        }

        return $options;
    }
}
