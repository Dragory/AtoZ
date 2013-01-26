<?php

class BaseFrontController extends BaseController
{
    protected $user = null;
    public $layout = 'layouts.front';

    public function before()
    {
        $this->layout->menu = null;
        $this->layout->notifications = null;
        $this->layout->content = null;
    }

    protected function loadPage($view, $data = [])
    {
        $data['user'] = $this->user;

        $this->layout->nest('menu', 'partials.menu', ['user' => $this->user]);
        $this->layout->nest('notifications', 'partials.notifications', [
            'errors' => Notification::message('error'),
            'successes' => Notification::message('success'),
            'informations' => Notification::message('information')
        ]);

        $this->layout->nest('content', $view, $data);
    }
}