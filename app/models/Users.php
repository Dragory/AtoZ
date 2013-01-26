<?php

class Users extends Base_DatabaseModel
{
    public function query()
    {
        return DB::table('users');
    }

    public function singleBySteam64($steam64)
    {

    }
}