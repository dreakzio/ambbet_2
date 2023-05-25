<?php
class send_line_message{
	function sendline_deposit($data,$token){
		//print_r($data);
		$strAccessToken = trim($token);
		$web_name  = $data['web_name'];
		$bank_tf_name  = $data['bank_tf_name'];
		$bank_tf_number  = $data['bank_tf_number'];
		$balance  = $data['balance'];
		$bank_time  = $data['bank_time'];
		$credit_after  = $data['credit_after'];
		$url_login  = $data['url_login'];
		$strUrl = "https://api.line.me/v2/bot/message/push";
		if($data['type_tran']==1){
			$text = "แจ้งเตื่อนการฝากเงิน";
			$text2 ='โอนจาก';
		}elseif($data['type_tran']==2){
			$text = "อนุมัติถอนเงิน";
			$text2 ='โอนไป';
		}
		$arrHeader = array();
		$arrHeader[] = "Content-Type: application/json";
		$arrHeader[] = "Authorization: Bearer {$strAccessToken}";
		$flexDataJson = '{
                      "type": "flex",
                      "altText": "'.$text.'",
                      "contents": {
                                  "type": "bubble",
                                  "body": {
                                    "type": "box",
                                    "layout": "vertical",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "'.$text.'",
                                        "weight": "bold",
                                        "color": "#1DB446",
                                        "size": "sm"
                                      },
                                      {
                                        "type": "text",
                                        "text": "'.$web_name.'",
                                        "weight": "bold",
                                        "size": "xxl",
                                        "margin": "md"
                                      },
                                      {
                                        "type": "separator",
                                        "margin": "xxl"
                                      },
                                      {
                                        "type": "box",
                                        "layout": "vertical",
                                        "margin": "xxl",
                                        "spacing": "sm",
                                        "contents": [
                                          {
                                            "type": "box",
                                            "layout": "horizontal",
                                            "contents": [
                                              {
                                                "type": "text",
                                                "text": "โอน'.$text2.' '.$bank_tf_name.' ('.$bank_tf_number.')",
                                                "size": "sm",
                                                "color": "#555555",
                                                "flex": 0
                                              },
                                              {
                                                "type": "text",
                                                "text": "'.$balance.' บาท",
                                                "size": "sm",
                                                "color": "#111111",
                                                "align": "end"
                                              }
                                            ]
                                          },
                                          {
                                            "type": "box",
                                            "layout": "horizontal",
                                            "contents": [
                                              {
                                                "type": "text",
                                                "text": "เวลา",
                                                "size": "sm",
                                                "color": "#555555",
                                                "flex": 0
                                              },
                                              {
                                                "type": "text",
                                                "text": "'.$bank_time.' น.",
                                                "size": "sm",
                                                "color": "#111111",
                                                "align": "end"
                                              }
                                            ]
                                          },
                                          {
                                            "type": "box",
                                            "layout": "horizontal",
                                            "contents": [
                                              {
                                                "type": "text",
                                                "text": "เครดิตคงเหลือ",
                                                "size": "sm",
                                                "color": "#555555",
                                                "flex": 0
                                              },
                                              {
                                                "type": "text",
                                                "text": "'.$credit_after.' บาท",
                                                "size": "sm",
                                                "color": "#111111",
                                                "align": "end"
                                              }
                                            ]
                                          },
                                          {
                                            "type": "separator",
                                            "margin": "xxl"
                                          },
                                          {
                                            "type": "box",
                                            "layout": "vertical",
                                            "contents": [
                                              {
                                                "type": "button",
                                                "action": {
                                                  "type": "uri",
                                                  "label": "เข้าสู่ระบบ",
                                                  "uri": "'.$url_login.'"
                                                }
                                              }
                                            ]
                                          }
                                        ]
                                      },
                                      {
                                        "type": "separator",
                                        "margin": "xxl"
                                      }
                                    ]
                                  },
                                  "styles": {
                                    "footer": {
                                      "separator": true
                                    }
                                  }
                                }
                     }';
		$flexDataJsonDeCode = json_decode($flexDataJson,true);
		$messages['to'] = $data['linebot_userid'];
		$messages['messages'][] = $flexDataJsonDeCode;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$strUrl);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $arrHeader);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messages));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		curl_close ($ch);
		//print_r($result);
	}
}
?>
