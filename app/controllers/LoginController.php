<?php

class LoginController extends BaseController
{
    protected $openID = null;

    public function __construct()
    {
        parent::__construct();

        $this->openID = new \Mivir\OpenID(null, URL::route('login_after'));
    }

    /**
     * Redirects the user to the OpenID authentication page.
     * The user should be returned to the route login_after.
     * @return Redirect
     */
    public function login()
    {
        return Redirect::to($this->openID->getAuthURL());
    }

    /**
     * The user is returned here from the OpenID authentication.
     * Checks whether the authentication was successful and
     * attempts to register and log the user in on the website.
     * @return Redirect
     */
    public function login_after()
    {
        $identity = $this->openID->getCurrentIdentity();
        if (!$identity)
        {
            return Redirect::route('index');
        }

        $steam64 = explode('/', $identity);
        $steam64 = array_pop($steam64);

        // Check if we already have the logged in user on record
        $users = new Users;
        $user = $users->getUserBySteam64($steam64);

        // If we're not yet in the database, register.
        if (!$user) $users->register($steam64);

        // Log in
        $status = $users->login($steam64);

        // If everything was successful, redirect to the dashboard
        if ($status)
        {
            return Redirect::to_route('index');
        }

        // Otherwise, redirect to the login page
        // and tell the user something went wrong.
        return Redirect::to_route('login');
    }
}