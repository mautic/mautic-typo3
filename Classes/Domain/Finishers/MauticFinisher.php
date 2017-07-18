<?php
declare(strict_types = 1);

/*
* This source file is proprietary property of Beech.it
* Date: 13-4-17
* All code (c) Beech Applications B.V. all rights reserved
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

namespace BeechIt\Mautic\Domain\Finishers;

use BeechIt\Mautic\Service\MauticService;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;


class MauticFinisher extends AbstractFinisher
{
    private $mauticService;

    /**
     * MauticFinisher constructor.
     */
    function __construct()
    {
        $this->mauticService = new MauticService();
    }

    /**
     * @return void
     */
    protected function executeInternal()
    {
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition()->getRenderingOptions();

        if (!empty($formDefinition['mauticId'])) {

            // Get the values that were posted in the form and transform them to a format for Mautic
            $formValues = $this->transformFormStructure($this->finisherContext->getFormValues());

            $this->pushMauticForm($formValues, $this->mauticService->getConfigurationData('mauticUrl'), $formDefinition['mauticId']);

        } else {
            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump('not meeting requirements for finisher mautic', 'ExecuteInternal');
        }
    }

    /**
     * Push data to a Mautic form
     *
     * @param  array $formStructure The data submitted by your form
     * @param  string $mauticUrl URL of the mautic installation
     * @param  integer $formId Mautic Form ID
     * @param  string $ip IP address of the lead
     * @return boolean
     */
    function pushMauticForm($formStructure, $mauticUrl, $formId, $ip = null)
    {
        // Get IP from $_SERVER
        if (!$ip) {
            $ipHolders = array(
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR'
            );
            foreach ($ipHolders as $key) {
                if (!empty($_SERVER[$key])) {
                    $ip = $_SERVER[$key];
                    if (strpos($ip, ',') !== false) {
                        // Multiple IPs are present so use the last IP which should be the most reliable IP that last connected to the proxy
                        $ips = explode(',', $ip);
                        array_walk($ips, create_function('&$val', '$val = trim($val);'));
                        $ip = end($ips);
                    }
                    $ip = trim($ip);
                    break;
                }
            }
        }

        $formStructure['formId'] = $formId;

        // return has to be part of the form data array
        if (!isset($formStructure['return'])) {
            $formStructure['return'] = $_SERVER['HTTP_HOST'];
        }

        // Build and initiate the POST
        $formStructurePost = array('mauticform' => $formStructure);
        $formUrl = $mauticUrl . '/form/submit?formId=' . $formId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $formUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formStructurePost));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Forwarded-For: $ip"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }


    /**
     * @param array $formStructure
     * @return array
     */
    private function transformFormStructure(array $formStructure): array
    {
        // Remove null values from the array
        $formStructure = array_filter($formStructure, function ($var) {
            return !is_null($var);
        });

        // Remove empty data so that the post request looks decent
        foreach (array_keys($formStructure, '', true) as $key) {
            unset($formStructure[$key]);
        }

        $toReturn = [];
        // Recreate the array with the Id's of the Mautic fields as Mautic has an oblivious lock on field identifiers
        foreach ($formStructure as $key => $value) {
            // Substitute the TYPO3identifier with the Mautic Alias
            $properties = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier($key)->getProperties();
            if (!empty($properties['mauticAlias'])) {
                $toReturn[$properties['mauticAlias']] = $value;
            }
        }

        return $toReturn;
    }

}