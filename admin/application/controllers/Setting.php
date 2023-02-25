<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setting extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != roleSuperAdmin()) {
            redirect('../admin');
        }
    }
    public function web_setting()
    {
		$gg_2fa_status = $this->Setting_model->setting_find([
			'name' => 'gg_2fa_status'
		]);
		$gg_2fa_secret = $this->Setting_model->setting_find([
			'name' => 'gg_2fa_secret'
		]);
		if($gg_2fa_status == ""){
			$this->Setting_model->web_setting_create([
				'name' => 'gg_2fa_status',
				'value' => '',
			]);
		}
		if($gg_2fa_secret == ""){
			$secret = $this->google_authenticator_librarie->createSecret();
			$this->Setting_model->web_setting_create([
				'name' => 'gg_2fa_secret',
				'value' => base64_encode(encrypt($secret,$this->config->item('secret_key_salt'))),
			]);
		}
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "ตั้งค่าเว็ป",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
        // $data['web_setting'] = $this->Setting_model->web_setting_find();
        $data['page'] = 'setting/web_setting';
        $this->load->view('main', $data);
    }
    public function web_setting_update()
    {
        // check_parameter([
        // 'line',
        // 'logo',
        // 'title',
        // 'web_description',
        // 'status'
        // ], 'POST');
        $post = $this->input->post();
        $update = [];
		if(isset($post['web_setting']['gg_2fa_gen']) && $post['web_setting']['gg_2fa_gen'] == "Y"){
			$secret = $this->google_authenticator_librarie->createSecret();
			$post['web_setting']['gg_2fa_secret'] = base64_encode(encrypt($secret,$this->config->item('secret_key_salt')));
			$_SESSION['user']['gg_2fa_chk'] = true;
			$_SESSION['user']['gg_2fa_secret'] = base64_encode(encrypt($secret,$this->config->item('secret_key_salt')));
		}else if(isset($post['web_setting']['gg_2fa_secret']) && !empty($post['web_setting']['gg_2fa_secret'])){
			$post['web_setting']['gg_2fa_secret'] = base64_encode(encrypt($post['web_setting']['gg_2fa_secret'],$this->config->item('secret_key_salt')));
		}
        foreach ($post['web_setting'] as $key => $value) {
          $update[] =  [
            'name' => $key,
            'value' => trim($value)
          ];
        }
		$web_logo = $this->save_image("web_logo");
        if(!empty($web_logo)){
			$update[] = [
				"name" => "web_logo",
				"value" =>str_replace("/admin","",base_url('assets/images/')).$web_logo,
			];
		}
		$web_logo_cover = $this->save_image("web_logo_cover");
		if(!empty($web_logo_cover)){
			$update[] = [
				"name" => "web_logo_cover",
				"value" =>str_replace("/admin","",base_url('assets/images/')).$web_logo_cover,
			];
		}
		$web_logo_cover_m = $this->save_image("web_logo_cover_m");
		if(!empty($web_logo_cover_m)){
			$update[] = [
				"name" => "web_logo_cover_m",
				"value" =>str_replace("/admin","",base_url('assets/images/')).$web_logo_cover_m,
			];
		}
		$web_sound_alert = $this->save_file("web_sound_alert");
		if(!empty($web_sound_alert)){
			$update[] = [
				"name" => "web_sound_alert",
				"value" =>str_replace("/admin","",base_url('assets/images/')).$web_sound_alert,
			];
		}
        $this->db->update_batch('web_setting', $update, 'name');
        $this->session->set_flashdata('toast', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
        redirect('setting/web_setting');
    }
	public function web_setting_2fa_gen()
	{
		$secret = $this->google_authenticator_librarie->createSecret();
		$this->db->update_batch('web_setting', [[
			'name' => 'gg_2fa_secret',
			'value' => base64_encode(encrypt($secret,$this->config->item('secret_key_salt')))
		],[
			'name' => 'gg_2fa_status',
			'value' => '1',
		]], 'name');
		$_SESSION['user']['gg_2fa_chk'] = true;
		$_SESSION['user']['gg_2fa_secret'] = base64_encode(encrypt($secret,$this->config->item('secret_key_salt')));
		$web_name = $this->Setting_model->setting_find([
			'name' => 'web_name'
		]);
		echo json_encode([
			'message' => 'สร้างใหม่เรียบร้อย, สามาถใช้แอพ Scan Qrcode ได้เลย',
			'result' => true,
			'gg_2fa_secret' => $secret,
			'img_2fa_qrcode' => $this->google_authenticator_librarie->getQRCodeGoogleUrl($web_name['value'], $secret),
		]);
	}
	public function save_image($name)
	{
		$type_file = pathinfo($_FILES[$name]["name"], PATHINFO_EXTENSION);
		if($name == "web_logo"){
			$rename = "main_logo_".date('YmdHis').".".$type_file;
			$config['width']     = 800;
			$config['height']   = 190;
		}else if($name == "web_logo_cover"){
			$rename = "img_member-bg-pc".date('YmdHis').".".$type_file;
			$config['width']     = 1980;
			$config['height']   = 440;
		}else if($name == "web_logo_cover_m"){
			$rename = "img_member-bg-m".date('YmdHis').".".$type_file;
			$config['width']     = 1000;
			$config['height']   = 375;
		}
		$config['upload_path']          = FCPATH.'/../assets/images';
		$config['allowed_types']        = 'gif|jpg|png|jpeg';
		$config['file_name']           = $rename;
		//resize
		$config['image_library'] = 'gd2';
		$config['source_image'] = $config['upload_path'].$rename;
		$config['maintain_ratio'] = TRUE;

		// $this->upload->clear();
		$this->upload->initialize($config);
		$this->load->library('upload', $config);
		if ($_FILES[$name]['error']==0) {
			if($this->upload->do_upload($name)){
				$this->image_lib->clear();
				$this->image_lib->initialize($config);
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				return $rename;
			}else{
				echo $this->upload->display_errors();
				exit();
			}
		}
	}

	public function save_file($name)
	{
		$type_file = pathinfo($_FILES[$name]["name"], PATHINFO_EXTENSION);
		if($name == "web_sound_alert"){
			$rename = "web_sound_alert".date('YmdHis').".".$type_file;
		}
		$config['upload_path']          = FCPATH.'/../assets/images';
		$config['allowed_types']        = 'gif|jpg|png|jpeg|mp3';
		$config['file_name']           = $rename;
		//resize
		$config['image_library'] = 'gd2';
		$config['source_image'] = $config['upload_path'].$rename;
		$config['maintain_ratio'] = TRUE;

		// $this->upload->clear();
		$this->upload->initialize($config);
		$this->load->library('upload', $config);
		if ($_FILES[$name]['error']==0) {
			if($this->upload->do_upload($name)){
				$this->image_lib->clear();
				$this->image_lib->initialize($config);
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				return $rename;
			}else{
				echo $this->upload->display_errors();
				exit();
			}
		}
	}

	public function check_sms_credit()
	{
		$credit = $this->sms_librarie->get_credit();
		echo json_encode([
			'message' => 'success',
			'result' => $credit["credit"]
		],JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
	}
}
