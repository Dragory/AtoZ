<?php namespace Mivir;

/**
 * A helper for the LightOpenID library.
 */
class OpenID
{
    private $openid = null, // The OpenID handler
            $defaultUrl = 'http://steamcommunity.com/openid'; // The OpenID provider

    public function __construct($url = null, $returnUrl = null)
    {
        // If no URL is supplied, use the default one
        if (!$url) $url = $this->defaultUrl;

        // Create the object and use our current URL as the "trusted root"
        $this->openid = new \LightOpenID(\URL::to('/'));
        if ($returnUrl) $this->openid->returnUrl = $returnUrl;

        // Use the URL as the identity
        $this->openid->identity = $url;
    }

    public function getCurrentIdentity()
    {
        if ($this->openid->validate()) return $this->openid->identity;
        return null;
    }

    public function getAuthURL()
    {
        return $this->openid->authUrl();
    }

    public function getOID()
    {
        return $this->openid;
    }
}