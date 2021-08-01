<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelAuth extends Model
{
    public function postDataRegister($username, $email, $password, $mobileNumber, $uniqId)
    {
        $query = $this->db->query(
            "INSERT INTO tb_users(username, email, password, mobile, created_at, uniq_id) 
            VALUES('$username', '$email', '$password', '$mobileNumber', NOW(), '$uniqId')"
        );
        return $query;
    }

    public function getEmailCheck($email)
    {
        $query = $this->db->query(
            "SELECT * FROM tb_users WHERE email='$email'"
        );
        return $query->getRowArray();
    }

    public function postExpiredTimePass($uniqId)
    {
        $query = $this->db->query(
            "UPDATE tb_users SET forgpass_date=NOW() WHERE uniq_id='$uniqId'"
        );
        return $query;
    }

    public function getUniqIdUsers($uniqId)
    {
        $query = $this->db->query(
            "SELECT * FROM tb_users WHERE uniq_id='$uniqId'"
        );
        return $query->getRowArray();
    }

    public function postActivateAccount($uniqId)
    {
        $query = $this->db->query(
            "UPDATE tb_users SET status='active', activation_date=NOW() WHERE uniq_id='$uniqId'"
        );
        return $query;
    }

    public function postNewPassword($uniqId, $password)
    {
        $query = $this->db->query(
            "UPDATE tb_users SET password='$password'
            WHERE uniq_id='$uniqId'"
        );
        return $query;
    }
}
