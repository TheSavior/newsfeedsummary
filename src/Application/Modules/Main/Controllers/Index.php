<?php
namespace Application\Modules\Main\Controllers;

class Index extends \Saros\Application\Controller
{
    private $storage;
    private $service;

    public function init() {
        $this->storage = new \Saros\Session("storage");

        if(!$GLOBALS["registry"]->fb->isAuthorized()) {
            // If they aren't signed in, redirect them
            $this->redirect($GLOBALS["registry"]->utils->makeLink("Login", "index"));
        }

        $this->service = $GLOBALS["registry"]->fb->getService();

        if (!isset($this->storage["personal"]))
        {
            $result = json_decode( $this->service->request( '/me' ), true );
            $this->storage["personal"] = $result;
        }
    }

	public function indexAction($start = null, $end = null)
	{
        if ($start) {
            $start = strtotime($start);
        }

        if ($end) {
            $end = strtotime($end);
        }

        if (!$start) {
            $start = strtotime("1 week ago");
        }

        if (!$end) {
            $end = strtotime("now");
        }
        

        $fbHistory = new \Application\Classes\DataGrabber($this->service);
        $data = $fbHistory->getData($start, $end); // currently doesn't use dates
        $this->view->Types = $fbHistory->run($data);
	}


    public function logoutAction() {
        $GLOBALS["registry"]->fb->resetSession();
        $this->storage->clear();
        $this->redirect($GLOBALS["registry"]->utils->makeLink("Index"));
    }
}
