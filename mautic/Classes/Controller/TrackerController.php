<?php
class Tx_Mautic_Controller_TrackerController extends Tx_Extbase_MVC_Controller_ActionController {

    public function initializeAction() {

    }

    /**
     * @return string The rendered view
     */
    public function indexAction() {
        $this->view->assign('result', array('test' => $this->settings['url']));
    }
}