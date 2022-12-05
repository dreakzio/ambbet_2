<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mamanee extends CI_Controller
{
    public function index()
    {
        redirect('auth');
    }
    public function maneeQR()
    {
        $qrcode_amount = $_REQUEST['qrcode_amount'];
        do {
            $refGen = $this->genToken();
            $account_amount = $this->Account_model->ref_manee_find([
                'ref_manee' => $refGen
            ]);
        } while ($account_amount != 0);
        $this->Account_model->ref_manee_update([
            'username' => $_SESSION['user']['username'],
            'ref_manee' => $refGen
        ]);
    
        $qrCode = $this->genTextQR($qrcode_amount,$refGen);
        echo json_encode([
            'qrcode' => $this->GenQR($qrCode),
            'amount' => $qrcode_amount
        ]);
    }

    public function genTextQR($amount,$ref2)
    {
        // $aid = "A000000677010112";
        // $billerid = "010753600010286";
        // $ref1 = "014000004042875";
        // $currency = "764";
        // $country = "TH";
        // $addlenght = "20";
        // $terminalid = "0000000000605985";
        $bank_manee = $this->Bank_model->manee();
        $aid = $bank_manee['aid'];
        $billerid = $bank_manee['billerid'];
        $ref1 = $bank_manee['ref1'];
        $currency = $bank_manee['currency'];
        $country = "TH";
        $addlenght = $bank_manee['addlenght'];
        $terminalid = $bank_manee['terminalid'];

        if (empty($amount) || $ref2 === "UNDEFINED") {
            exit;
        }

        if (strlen(strlen($ref2)) == 1) {
            $ref_count = "0" . strlen($ref2);
        } else {
            $ref_count = strlen($ref2);
        }

        if (strlen(strlen($amount)) == 1) {
            $amount_count = "0" . strlen($amount);
        } else {
            $amount_count = strlen($amount);
        }

        if (strlen(strlen($country)) == 1) {
            $country_count = "0" . strlen($country);
        } else {
            $country_count = strlen($country);
        }

        if (strlen(strlen($currency)) == 1) {
            $currency_count = "0" . strlen($currency);
        } else {
            $currency_count = strlen($currency);
        }

        $text = "00020101021230";
        $referer = "00" . strlen($aid) . $aid . "01" . strlen($billerid) . $billerid . "02" . strlen($ref1) . $ref1 . "03" . $ref_count . strtoupper($ref2);
        $text .= strlen($referer) . $referer;
        $text .= "58" . $country_count . $country . "54" . $amount_count . $amount . "53" . $currency_count . $currency . "62" . $addlenght . "07" . strlen($terminalid) . $terminalid . "6304";
        $text .= $this->crcChecksum($text);
        return $text;
    }

    private function crcChecksum($str)
    {
        function charCodeAt($str, $i)
        {
            return ord(substr($str, $i, 1));
        }

        $crc = 0xFFFF;
        $strlen = strlen($str);
        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= charCodeAt($str, $c) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        $hex = $crc & 0xFFFF;
        $hex = dechex($hex);
        $hex = strtoupper($hex);

        return $hex;
    }

    public function GenQR($text)
    {
        return 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $text . '&choe=UTF-8';
    }

    private function genToken()
	{
			$alphabet = "ABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
			$pass = array();
			$alphaLength = strlen($alphabet) - 1;
			for ($i = 0; $i < 20; $i++) {
				$n = rand(0, $alphaLength);
				$pass[] = $alphabet[$n];
			}
			return implode($pass);
	}
}
