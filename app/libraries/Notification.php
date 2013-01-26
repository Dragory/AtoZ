<?php

class Notification
{
    // Either gets or sets a notification of a certain type.
    public static function message($type, $text = null)
    {
        if (!Session::get('notifications')) Session::put('notifications', []);

        $notifications = Session::get('notifications');
        if (!array_key_exists($type, $notifications)) $notifications[$type] = [];

        // Setting
        if ($text)
        {
            $notifications[$type][] = $text;
            Session::put('notifications', $notifications);
        }
        // Getting
        else
        {
            return $notifications[$type];
        }
    }

    /**
     * Helper functions
     */

    public static function error($text)
    {
        self::notification('error', $text);
    }

    public static function success($text)
    {
        self::notification('success', $text);
    }

    public static function information($text)
    {
        self::notification('information', $text);
    }
}