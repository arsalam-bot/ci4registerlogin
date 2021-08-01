<?php

namespace App\Controllers;

use App\Models\ModelAuth;

class Home extends BaseController
{
	public $homeModel, $session;
	public function __construct()
	{
		$this->homeModel = new ModelAuth();
		$this->session = \Config\Services::session();
	}
	public function index()
	{
		$uniqId = $this->session->get('uniqId');

		$data = [
            'judul' => 'Sistem Login Register'
        ];
		$data['dataUsers'] = $this->homeModel->getUniqIdUsers($uniqId);
		echo view('welcome_message', $data, $data);
	}
}