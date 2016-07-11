<?php
use Illuminate\Database\Eloquent\Model;

class UserRegistered extends Model
{
    public $table = 'users_registered';
    public $primaryKey = 'username';
    public $timestamps = false;

    public function generateDeleteCode()
    {
        $this->delete_code = hash('sha256', (time() . $this->username . rand()));
    }
}