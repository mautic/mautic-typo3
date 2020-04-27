/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Florian Wessels <f.wessels@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 *
 ***/

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
        }

        /**
         * @private
         *
         * @return object
         */
        function getPublisherSubscriber() {
            return getFormEditorApp().getPublisherSubscriber();
        }

        /**
         * @private
         *
         * @return object
         */
        function getUtility() {
            return getFormEditorApp().getUtility();
        }

        /**
         * @private
         *
         * @return object
         */
        function getHelper() {
            return Helper;
        }

        /**
         * @private
         *
         * @return object
         */
        function getCurrentlySelectedFormElement() {
            return getFormEditorApp().getCurrentlySelectedFormElement();
        }

        /**
         * @private
         *
         * @param test mixed
         * @param message string
         * @param messageCode int
         * @return void
         */
        function assert(test, message, messageCode) {
            return getFormEditorApp().assert(test, message, messageCode);
        }

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
        }

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
             *          args[0] = editorConfiguration
             *          args[1] = editorHtml
             *          args[2] = collectionElementIdentifier
             *          args[2] = collectionName
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
             *          args[0] = formElement
             *          args[1] = template
             * @return void
             * @subscribe view/stage/abstract/render/template/perform
             */
            getPublisherSubscriber().subscribe('view/stage/abstract/render/template/perform', function (topic, args) {
                switch (args[0].get('type')) {
                    case  'HiddenDate':
                        StageComponent.renderSimpleTemplate(args[0], args[1]);
                        break;
                    case 'CountryList':
                        getFormEditorApp().getViewModel().getStage().renderSimpleTemplateWithValidators(args[0], args[1]);
                        break;
                }
            });
        }

        /**
         * @private
         * @see renderSingleSelectEditor
         *
         * @param editorConfiguration object
         * @param editorHtml object
         * @param collectionElementIdentifier string
         * @param collectionName string
         * @return void
         * @throws 1475421048
         * @throws 1475421049
         * @throws 1475421050
         * @throws 1475421051
         * @throws 1475421052
         */
        function renderMauticPropertySelectEditor(editorConfiguration, editorHtml, collectionElementIdentifier, collectionName) {
            var propertyData, propertyPath, selectElement;
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
            const options = $('option', selectElement);
            selectElement.empty();

            for (var i = 0, len = options.length; i < len; ++i) {
                var option;
                console.log(options[i]);

                if (options[i]['value'] === propertyData) {
                    option = new Option(options[i]['label'], i, false, true);
                } else {
                    option = new Option(options[i]['label'], i);
                }
                $(option).data({value: options[i]['value'], type: options[i]['data-type']});
                selectElement.append(option);
            }

            selectElement.on('change', function () {
                getCurrentlySelectedFormElement().set(propertyPath, $('option:selected', $(this)).data('value'));
            });
        }

        /**
         * @public
         *
         * @param  formEditorApp object
         * @param  additionalViewModelModules object
         * @return void
         */
        function bootstrap(formEditorApp, additionalViewModelModules) {
            _formEditorApp = formEditorApp;
            _helperSetup();
            _subscribeEvents();
        }

        /**
         * Publish the public methods.
         * Implements the "Revealing Module Pattern".
         */
        return {
            bootstrap: bootstrap
        };
    })($, StageComponent, Helper);
});
