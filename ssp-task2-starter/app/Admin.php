<?php
class Admin {
    public $id;
    public $username;
    public $email;
    public $password;

    public function __construct($id, $username, $email, $password) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }
}
