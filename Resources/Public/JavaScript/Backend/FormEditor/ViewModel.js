/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
/**
 * Module: TYPO3/CMS/ExtFormMarketingAutomatisation/Backend/FormEditor/ViewModel
 */
define(['jquery',
    'TYPO3/CMS/Form/Backend/FormEditor/StageComponent',
    'TYPO3/CMS/Form/Backend/FormEditor/Helper'
], function ($, StageComponent, Helper) {
    'use strict';
    return (function ($, StageComponent, Helper) {
        /**
         * @private
         *
         * @var object
         */
        var _formEditorApp = null;

        /**
         * @private
         *
         * @return object
         */
        function getFormEditorApp() {
            return _formEditorApp;
        };
        /**
         * @private
         *
         * @return object
         */
        function getPublisherSubscriber() {
            return getFormEditorApp().getPublisherSubscriber();
        };
        /**
         * @private
         *
         * @return object
         */
        function getUtility() {
            return getFormEditorApp().getUtility();
        };
        /**
         * @private
         *
         * @param object
         * @return object
         */
        function getHelper() {
            return Helper;
        };
        /**
         * @private
         *
         * @return object
         */
        function getCurrentlySelectedFormElement() {
            return getFormEditorApp().getCurrentlySelectedFormElement();
        };
        /**
         * @private
         *
         * @param mixed test
         * @param string message
         * @param int messageCode
         * @return void
         */
        function assert(test, message, messageCode) {
            return getFormEditorApp().assert(test, message, messageCode);
        };
        /**
         * @private
         *
         * @return void
         * @throws 1478268638
         */
        function _helperSetup() {
            assert('function' === $.type(Helper.bootstrap),
                'The view model helper does not implement the method "bootstrap"',
                1483708624
            );
            Helper.bootstrap(getFormEditorApp());
        };
        /**
         * @private
         *
         * @return void
         */
        function _subscribeEvents() {
            /**
             * @private
             *
             * @param string
             * @param array
             *		  args[0] = editorConfiguration
             *		  args[1] = editorHtml
             *		  args[2] = collectionElementIdentifier
             *		  args[2] = collectionName
             * @return void
             * @subscribe view/inspector/editor/insert/perform
             */
            getPublisherSubscriber().subscribe('view/inspector/editor/insert/perform', function (topic, args) {
                if (args[0]['templateName'] === 'Inspector-MauticPropertySelectEditor') {
                    renderMauticPropertySelectEditor(
                        args[0],
                        args[1],
                        args[2],
                        args[3]
                    );
                }
            });

            /**
             * @private
             *
             * @param string
             * @param array
             *		  args[0] = formElement
             *		  args[1] = template
             * @return void
             * @subscribe view/stage/abstract/render/template/perform
             */
            getPublisherSubscriber().subscribe('view/stage/abstract/render/template/perform', function(topic, args) {
                switch (args[0].get('type')) {
                    case  'HiddenDate':
                        StageComponent.renderSimpleTemplate(args[0], args[1]);
                        break;
                    case 'Email':
                    case 'CountryList':
                        getFormEditorApp().getViewModel().getStage().renderSimpleTemplateWithValidators(args[0], args[1]);
                        break;
                }
            });
        };

        /**
         * @private
         *
         * @param object editorConfiguration
         * @param object editorHtml
         * @param string collectionElementIdentifier
         * @param string collectionName
         * @return void
         * @throws 1475421048
         * @throws 1475421049
         * @throws 1475421050
         * @throws 1475421051
         * @throws 1475421052
         */
        function renderMauticPropertySelectEditor(editorConfiguration, editorHtml, collectionElementIdentifier, collectionName) {
            var options, propertyData, propertyPath, selectElement, values;
            assert(
                'object' === $.type(editorConfiguration),
                'Invalid parameter "editorConfiguration"',
                1475421048
            );
            assert(
                'object' === $.type(editorHtml),
                'Invalid parameter "editorHtml"',
                1475421049
            );
            assert(
                getUtility().isNonEmptyString(editorConfiguration['label']),
                'Invalid configuration "label"',
                1475421050
            );
            assert(
                getUtility().isNonEmptyString(editorConfiguration['propertyPath']),
                'Invalid configuration "propertyPath"',
                1475421051
            );
            propertyPath = getFormEditorApp().buildPropertyPath(
                editorConfiguration['propertyPath'],
                collectionElementIdentifier,
                collectionName
            );
            getHelper()
                .getTemplatePropertyDomElement('label', editorHtml)
                .append(editorConfiguration['label']);
            selectElement = getHelper()
                .getTemplatePropertyDomElement('selectOptions', editorHtml);
            propertyData = getCurrentlySelectedFormElement().get(propertyPath);
            options = $('option', selectElement);
            selectElement.empty();
            for (var i = 0, len = options.length; i < len; ++i) {
                var option;
                if (
                    options[i].value === propertyData
                    || (!propertyData && i === 0)
                ) {
                    option = new Option(options[i].text, i, false, true);
                } else {
                    option = new Option(options[i].text, i);
                }
                $(option).data({value: options[i].value});
                selectElement.append(option);
            }
            if (!propertyData) {
                getCurrentlySelectedFormElement().set(propertyPath, options[0].value);
            }
            selectElement.on('change', function () {
                getCurrentlySelectedFormElement().set(propertyPath, $('option:selected', $(this)).data('value'));
            });
        };
        /**
         * @public
         *
         * @param object formEditorApp
         * @param object additionalViewModelModules
         * @return void
         */
        function bootstrap(formEditorApp, additionalViewModelModules) {
            _formEditorApp = formEditorApp;
            _helperSetup();
            _subscribeEvents();
        };
        /**
         * Publish the public methods.
         * Implements the "Revealing Module Pattern".
         */
        return {
            bootstrap: bootstrap
        };
    })($, StageComponent, Helper);
});
