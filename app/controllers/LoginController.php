<?php

class LoginController extends BaseFrontController
{
    protected $openID = null;

    public function __construct()
    {
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

        // Parse the 64-bit SteamID from the returned identity
        $steam64 = explode('/', $identity);
        $steam64 = array_pop($steam64);

        // Required models for this function
        $usersModel = new Users;
        $middleAuthenticationModel = new \Mivir\MiddleAuthentication;

        // Check if we already have the logged in user on record
        $user = $usersModel->getUserBySteam64($steam64);

        // If we're not yet in the database, register.
        if (!$user)
        {
            try {
                $middleAuthenticationModel->register($steam64);
            } catch (\Mivir\RegistrationFailedException $e) {
                Notification::error($e->getMessage());
                return Redirect::route('index');
            }
        }

        // Log in
        try {
            $status = $users->login($steam64);
        } catch (\Mivir\InvalidLoginException $e) {
            Notification::error($e->getMessage());
            return Redirect::route('index');
        }

        // If everything was successful, redirect to the dashboard
        Notification::success(__('success.login_successful'));
        return Redirect::to_route('index');
    }
}