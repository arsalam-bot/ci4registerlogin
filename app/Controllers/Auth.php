<?php

namespace App\Controllers;

use App\Models\ModelAuth;

class Auth extends BaseController
{
    public $authModel, $session, $email, $validationError;
    public function __construct()
    {
        helper('date');
        $this->authModel = new ModelAuth();
        $this->session = \Config\Services::session();
        $this->email = \Config\Services::email();
        $this->validationError = \Config\Services::validation();
    }
    public function index()
    {
        $data['tittle_header'] = 'Login';
        echo view('auth/assets/assetsCSS', $data);
        echo view('auth/login');
        echo view('auth/assets/assetsJS');
    }

    public function register()
    {
        $data['tittle_header'] = 'Register Account';
        echo view('auth/assets/assetsCSS', $data);
        echo view('auth/register');
        echo view('auth/assets/assetsJS');
    }

    public function register_process()
    {
        $uniqId = md5(str_shuffle('abc' . time()));
        $username = $this->request->getVar('username');
        $email = $this->request->getVar('email');
        $password = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
        $confPassword = $this->request->getVar('conf_password');
        $mobileNumber = $this->request->getVar('mobile_number');

        if ($this->validate([
            'username' => [
                'label' => 'Username',
                'rules' => 'required|min_length[4]|max_length[20]',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'min_length' => '{field} at least four combinations of numbers and alphabet',
                    'max_length' => '{field} a maximum of twenty combinations of numbers and alphabet',
                ],
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[tb_users.email]',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'valid_email' => 'The {field} entered is not valid',
                    'is_unique' => '{field} already used',
                ],
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[6]|max_length[8]',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'min_length' => '{field} at least six combinations of numbers and alphabet',
                    'max_length' => '{field} a maximum of eight combinations of numbers and alphabet',
                ],
            ],
            'conf_password' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'matches' => '{field} doesnt mathces',
                ],
            ],
            'mobile_number' => [
                'label' => 'Mobile Number',
                'rules' => 'required|numeric',
                // 'rules' => 'required|exact_length[15]|numeric',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'numeric' => '{field} can only contain numbers',
                ],
            ],
        ])) {
            if ($this->authModel->postDataRegister($username, $email, $password, $mobileNumber, $uniqId)) {
                $to = $this->request->getVar('email');
                $subject = 'Account Activation Link';
                $message = 'Hi ' . $this->request->getVar('username') . "<br><br>"
                    . "Your account was created successfully. Please click"
                    . "the link below to activate your account. <br><br>"
                    . "<a href= '" . base_url() . "/auth/activate/" . $uniqId . "' target='_blank'> Activate Now</a><br><br>";
                $this->email->setTo($to);
                $this->email->setFrom('arsalammmmm1@gmail.com', 'Register Login Sistem');
                $this->email->setMessage($message);
                $this->email->setSubject($subject);
                if ($this->email->send()) {
                    $this->session->setFlashdata('success', "Your account was created successfully!!! Check your email to activate your account");
                    return redirect()->to(base_url('auth'));
                } else {
                    $this->session->setFlashdata('error', "there some errors");
                    return redirect()->to(base_url('auth/register'))->withInput();
                }
            }
        }
        $this->session->setFlashdata('error', $this->validationError->listErrors());
        return redirect()->to(base_url('auth/register'))->withInput();
    }

    public function activate($uniqId)
    {
        $data['tittle_header'] = 'Activate Your Account';
        $dataUsers = $this->authModel->getUniqIdUsers($uniqId);
        if ($dataUsers['uniq_id'] != null) {
            $uniqId = $uniqId;
            $dataUniqId['dataUsers'] = $this->authModel->getUniqIdUsers($uniqId);
            echo view('auth/assets/assetsCSS', $data);
            echo view('auth/activateaccount', $dataUniqId);
            echo view('auth/assets/assetsJS');
        }else {
            $this->session->setFlashdata('error', "Can not find your account");
            return redirect()->to(base_url('auth'));
        }
    }

    public function activation_proses($dataUniqId)
    {
        $uniqId = $dataUniqId;
        if ($this->authModel->postActivateAccount($uniqId)) {
            $this->session->setFlashdata('success', "Your account has been successfully activated!!! Please login to continue");
            return redirect()->to(base_url('auth'));
        }
    }

    public function login()
    {
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        if ($this->validate([
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'valid_email' => 'The {field} entered is not valid',
                ],
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[6]|max_length[8]',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'min_length' => '{field} at least six combinations of numbers and alphabet',
                    'max_length' => '{field} a maximum of eight combinations of numbers and alphabet',
                ],
            ]
        ])) {
            $dataUsers = $this->authModel->getEmailCheck($email);
            if ($dataUsers) {
                if (password_verify($password, $dataUsers['password'])) {
                    if ($dataUsers['status'] == 'active') {
                        $this->authModel = [
                            'uniqId' => $dataUsers['uniq_id'],
                            'logged_in' => true
                        ];
                        $this->session->set($this->authModel);
                        return redirect()->to(base_url('home'));
                    } else {
                        $this->session->setFlashdata('error', "Please activate your account!!!<br>Check your email to activate.");
                        return redirect()->to(base_url('auth'))->withInput();
                    }
                } else {
                    $this->session->setFlashdata('error', "Your email or password doesn't exist");
                    return redirect()->to(base_url('auth'))->withInput();
                }
            }
        }
        $this->session->setFlashdata('error', $this->validationError->listErrors());
        return redirect()->to(base_url('auth'))->withInput();
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to(base_url('auth'));
    }

    public function forgotpassword()
    {
        $data['tittle_header'] = 'Forgot Password';
        echo view('auth/assets/assetsCSS', $data);
        echo view('auth/forgotpass');
        echo view('auth/assets/assetsJS');
    }

    public function checkemail()
    {
        $email = $this->request->getVar('email');

        $dataUsers = $this->authModel->getEmailCheck($email);

        if (!empty($dataUsers)) {
            $this->authModel->postExpiredTimePass($dataUsers['uniq_id']);
            $to = $email;
            $subject = 'Reset Password Link';
            $token = $dataUsers['uniq_id'];
            $message = 'Hello ' . $dataUsers['username'] . "<br><br>"
                . "Your reset password request has been received. Please click "
                . "the link below to reset your password. <br><br>"
                . "<a href= '" . base_url() . "/auth/resetpassword/" . $token . "'>Reset Password</a><br><br>";
            $this->email->setTo($to);
            $this->email->setFrom('arsalammmmm1@gmail.com', 'Do Not Reply!!!');
            $this->email->setSubject($subject);
            $this->email->setMessage($message);
            if ($this->email->send()) {
                $this->session->setFlashdata('success', "The link to reset your password has been sent successfully!!!. Please check your email");
                return redirect()->to(base_url('auth'));
            }
        } else {
            $this->session->setFlashdata('error', "Can not find your email, please enter a valid email");
            return redirect()->to(base_url('auth/forgotpassword'))->withInput();
        }
    }

    public function resetpassword($uniqId)
    {
        $data['tittle_header'] = 'Reset Your Password';
        $uniqId = $uniqId;
        $dataUniqId['dataUsers'] = $this->authModel->getUniqIdUsers($uniqId);
        $usersData = $this->authModel->getUniqIdUsers($uniqId);
        $getTime = $usersData['forgpass_date'];
        $expTime = strtotime($getTime);
        $currTime = now();
        $diffTime = ((int)$currTime - (int)$expTime) * -1;
        /* the result of the calculation stored in $diffTime is approximately 46790.
           So I subtracted it by 120 (in seconds) which is 2 minutes. The number 46660 
           is found from the results of the subtraction which is used as a comparison.
           ---PLEASE CORRECT ME IF I'M WRONG---
        */
        if (46660 < $diffTime) {
            echo view('auth/assets/assetsCSS', $data);
            echo view('auth/resetpassword', $dataUniqId);
            echo view('auth/assets/assetsJS');
        } else {
            session()->setFlashdata('error', 'Link Reset Password Expired');
            return redirect()->to(base_url('auth'));
        }
    }

    public function reset_password_process($dataUniqId)
    {
        $uniqId = $dataUniqId;
        $password = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
        $confPassword = $this->request->getVar('conf_password');
        if ($this->validate([
            'password' => [
                'label' => 'New Password',
                'rules' => 'required|min_length[6]|max_length[8]',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'min_length' => '{field} at least six combinations of numbers and alphabet',
                    'max_length' => '{field} a maximum of eight combinations of numbers and alphabet',
                ],
            ],
            'conf_password' => [
                'label' => 'Confirm New Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => '{field} cannot be empty',
                    'matches' => '{field} doesnt mathces with new password',
                ],
            ],
        ])) {
            if ($this->authModel->postNewPassword($uniqId, $password)) {
                $this->session->setFlashdata('success', "Password has been successfully modified!!!");
                return redirect()->to(base_url('auth'));
            } else {
                $this->session->setFlashdata('error', "Failed to modified your password!!!");
                return redirect()->to(base_url('auth/resetpassword/' . $uniqId));
            }
        }
        $this->session->setFlashdata('error', $this->validationError->listErrors());
        return redirect()->to(base_url('auth/resetpassword/' . $uniqId))->withInput();
    }
}

// $email = \Config\Services::email();
// $email->setTo('launualam@gmail.com');
// $email->setFrom('arsalammmmm1@gmail.com', 'Do Not Reply!!!');
// $email->setSubject('Kirim Email');
// $email->setMessage('Hello');
// if ($email->send()) {
//     echo "sukses kirim email";
// } else {
//     echo "gagal kirim email";
// }
