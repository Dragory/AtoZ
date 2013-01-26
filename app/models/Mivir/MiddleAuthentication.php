<?php namespace Mivir;

/**
 * An authentication class to be used with e.g. OpenID logins.
 * This class doesn't handle passwords by itself, but instead
 * registers and logs users in using a single identifier. This identifier
 * can be returned by other authentication systems, such as OpenID.
 * In other words, the AUTHENTICATION process is handled by an external source.
 * This class acts as a middle-man between that process and the application's database.
 */
class MiddleAuthentication
{
    // The login token's name (session/cookie)
    protected $tokenName = 'loginToken';

    // Database settings
    protected $database = [
        // The table to use
        'table' => 'users',
        // The name of the ID row of the users table
        'id' => 'id_user',
        // The name of the token row of the users table
        'token' => 'user_token'
        // The name of the identifier row of the users table
        'identifier' => 'user_identifier'
    ];

    /**
     * Checks if the user is logged in and if yes,
     * returns that user's ID.
     * @return mixed The user's ID when they are logged in, null when they are not
     */
    public function getCurrentUser()
    {
        // Get the login token
        $token = \Session::get($this->tokenName);
        if ($token == null) $token = \Cookie::get($this->tokenName);
        if ($token == null) return null;

        // See if a user exists with the given login token
        $user = \DB::table($this->database['table'])
            ->where($this->database['token'], '=', $token)
            ->first();

        // If a user was not found for the login token,
        // remove the login token from Session and the Cookie.
        if ($user == null)
        {
            \Session::forget($this->tokenName);
            \Cookie::forget($this->tokenName);

            return null;
        }

        // If a user DOES exist with that login token,
        // we should be fine to log in. Probably.
        // Also refresh the token in the Session.
        \Session::put($this->tokenName, $token);

        // If the user has chosen to stay logged in,
        // also refresh the cookie.
        if (\Cookie::get('stayLogged') != null)
            \Cookie::put($this->tokenName, $token);

        $sqlId = $this->database['id'];
        return $user->$sqlId;
    }

    /**
     * Logs the user in by their identifier.
     * @param  string $identifier The identifier.
     * @return null
     */
    public function login($identifier)
    {
        $rowId         = $this->database['id'];
        $rowToken      = $this->database['token'];
        $rowIdentifier = $this->database['identifier'];

        // Find the user by their identifier
        $user = \DB::table($this->database['table'])
            ->where($rowIdentifier, '=', $identifier)
            ->first();

        // If the user was not found, throw an exception
        if (!$user) throw new \Mivir\InvalidLoginException('User was not found in the database.');

        // Get the user's login token
        $token = $user->$rowToken;

        // If no token was found, generate one
        // and put it into the database with the user.
        if (!$token)
        {
            $token = $this->generateLoginToken();
            \DB::table($this->database['table'])
                ->where($rowId, '=', $user->$rowId)
                ->update([
                    $rowToken => $token
                ]);
        }

        // Log us in by setting the token in the session and/or cookie
        Session::put($this->tokenName, $token);
        Cookie::put($this->tokenName, $token);
    }

    /**
     * Logs the user out.
     * @return null
     */
    public function logout()
    {
        \Session::forget($this->tokenName);
        \Cookie::forget($this->tokenName);
    }

    /**
     * Creates an account with the given identifier.
     * @param  string $identifier  The identifier
     * @return int                 The row ID of the registered user
     */
    public function register($identifier)
    {
        // Make sure an account doesn't already exist with this identifier
        $user = \DB::table($this->database['table'])
            ->where($this->database['identifier'], '=', $identifier)
            ->first();

        if ($user) throw new \Mivir\RegistrationFailedException('A duplicate identifier was found in the database.');

        // If we're creating a new unique account, create it
        $id = \DB::table($this->database['table'])
            ->insert_get_id([
                $this->database['identifier'] => $identifier,
                $this->database['token'] => \DB::raw('NULL')
            ]);

        if (!$id) throw new \Mivir\RegistrationFailedException('A server error occurred while registering the user.');

        return $id;
    }

    /**
     * Generates a login token to avoid storing usernames
     * and/or passwords and/or password hashes in the session
     * and/or cookies. This can also be deleted to log the
     * user out everywhere they've logged in from.
     * 
     * @param  int    $length  Length of the login token
     * @return string          The login token
     */
    private function generateLoginToken($length = 64)
    {
        $token = '';
        $isUnique = false;
        $tokenCharsLastIndex = strlen($this->tokenChars)-1;

        // While we have not ended up with a valid
        // and unique token
        while (!$isUnique)
        {
            $token = '';

            // Generate the token
            for ($i = 0; $i < $length; $i++)
                $token .= $this->tokenChars[mt_rand(0, $tokenCharsLastIndex)];

            // Check if it exists in the database
            $tokenUser = \DB::table($this->database['table'])
                ->where($this->database['token'], '=', $token)
                ->first();

            if ($tokenUser == null) $isUnique = true;
        }

        // Once we got to a valid token, return it
        return $token;
    }
}