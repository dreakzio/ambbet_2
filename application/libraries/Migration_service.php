<?php
class Migration_service
{
	private $db;
	private $version = 12;
	private $strFileName = "update.txt";
	public function __construct()
	{

	}

	private function insertData($table,$field,$value){
		//global $obj_con_cron;
		$sqlCheck ="Select * from {$table} where {$field[0]} ='{$value[0]}'";
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() ==0){

			foreach ($field as $field_data){
				$txt_field .= ",{$field_data}";
			}
			$txt_field = substr($txt_field,1,strlen($txt_field));
			$txt_field = "({$txt_field})";

			//$txt_val = " VALUES(";
			foreach ($value as $val){
				$txt_val .= ",'{$val}'";
			}

			$txt_val = substr($txt_val,1,strlen($txt_val));
			$txt_val = " VALUES({$txt_val})";

			$sqlInsert = " INSERT INTO {$table} {$txt_field} {$txt_val}";
			$this->db->query($sqlInsert);
		}
	}
	private function insertDataRaw($table,$column_name_duplicate_chk,$column_value_duplicate_chk,$column_name_duplicate_chk_2,$column_value_duplicate_chk_2,$sql_insert_raw){
		//global $obj_con_cron;
		$sqlCheck ="Select * from {$table} where {$column_name_duplicate_chk} ".(is_null($column_value_duplicate_chk) ? "IS NULL" : "='{$column_value_duplicate_chk}'").(!empty($column_name_duplicate_chk_2) ? " AND {$column_name_duplicate_chk_2} ".(is_null($column_value_duplicate_chk_2) ? "IS NULL" : "='{$column_value_duplicate_chk_2}'") : "");
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() ==0){
			$this->db->query($sql_insert_raw);
		}
	}
	private function addColumn($table_name,$column_name,$option){
		if (!$this->db->field_exists($column_name, $table_name))
		{
			$this->db->query("ALTER TABLE {$table_name} ADD {$column_name} {$option}");
			//echo "Add Column {$column_name} to {$table_name} Success !!!! <br/>";
		}
	}
	private function addColumnRaw($table_name,$column_name,$column_name_raw,$option){
		if (!$this->db->field_exists($column_name, $table_name))
		{
			$this->db->query("ALTER TABLE {$table_name} ADD {$column_name_raw} {$option}");
			//echo "Add Column {$column_name} to {$table_name} Success !!!! <br/>";
		}
	}
	private function addColumnUnique($table_name,$column_name,$key_name,$option){
		$sqlCheck ="SHOW INDEX FROM {$table_name} where Key_name ='{$key_name}'";
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() ==0){
			$this->db->query("ALTER TABLE {$table_name} ADD {$column_name} {$option}");
			//echo "Add Column {$column_name} to {$table_name} Success !!!! <br/>";
		}
	}
	private function addColumnConstraint($table_name,$column_name,$constraint_name,$option){
		$sqlCheck ="SELECT * FROM information_schema.table_constraints where TABLE_NAME ='{$table_name}' AND CONSTRAINT_NAME = '{$constraint_name}'";
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() ==0){
			$this->db->query("ALTER TABLE {$table_name} ADD {$column_name} {$option}");
			//echo "Add Column {$column_name} to {$table_name} Success !!!! <br/>";
		}
	}
	private function updateDataRaw($table,$column_name_duplicate_chk,$column_value_duplicate_chk,$column_name_duplicate_chk_2,$column_value_duplicate_chk_2,$sql_insert_raw){
		//global $obj_con_cron;
		$sqlCheck ="Select * from {$table} where {$column_name_duplicate_chk} ".(is_null($column_value_duplicate_chk) ? "IS NULL" : "='{$column_value_duplicate_chk}'").(!empty($column_name_duplicate_chk_2) ? " AND {$column_name_duplicate_chk_2} ".(is_null($column_value_duplicate_chk_2) ? "IS NULL" : "='{$column_value_duplicate_chk_2}'") : "");
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() >= 1){
			$this->db->query($sql_insert_raw);
		}
	}
	private function deleteDataRaw($table,$sql_delete_raw){
		$this->db->query($sql_delete_raw);
	}
	private function checkTableExist($table_name){
		return $this->db->table_exists($table_name);
	}
	private function checkRowCount($table_name){
		if(!$this->db->table_exists($table_name)){
			return null;
		}
		$sqlCheck ="Select * from {$table_name} where 1";
		$query =$this->db->query($sqlCheck);
		return $query->num_rows();
	}
	private function createTable($table_name,$sql_column_and_key_raw,$option = "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"){
		if (!$this->db->table_exists($table_name))
		{
			$this->db->query("CREATE TABLE `{$table_name}` (
					  {$sql_column_and_key_raw}
					) {$option}
			");
			//echo "Create table {$table_name} Success !!!! <br/>";
		}
	}

	public function init(){

		$file = file_get_contents($this->strFileName, true);
		if($file != $this->version){

			$objFopen = fopen($this->strFileName, 'w');
			fwrite($objFopen, $this->version);

			$CI = &get_instance();
			$this->db = &$CI->db;

			$this->addColumn('account_agent','username_after'," VARCHAR(100) NULL");
			$this->addColumn('account_agent','password_after'," VARCHAR(100) NULL");
			$this->addColumn('account_agent','credit_after'," double(10,2) NULL");
			$this->addColumn('account_agent','status',"int(1) NOT NULL DEFAULT 0");

			$this->addColumn('finance','is_auto_withdraw',"TINYINT(1) NOT NULL DEFAULT '0' AFTER `manage_by`");
			$this->addColumn('finance','auto_withdraw_status',"TINYINT(1) NULL AFTER `is_auto_withdraw`");
			$this->addColumn('finance','auto_withdraw_remark',"TEXT NULL AFTER `auto_withdraw_status`");
			$this->addColumn('finance','auto_withdraw_created_at',"timestamp NULL DEFAULT NULL AFTER `auto_withdraw_remark`");
			$this->addColumn('finance','auto_withdraw_updated_at',"timestamp NULL DEFAULT NULL AFTER `auto_withdraw_created_at`");
			$this->addColumn('account','is_auto_withdraw',"tinyint(1) NULL DEFAULT 1 AFTER auto_accept_bonus");


			$this->insertData('web_setting',['name','value'],['manual_linenoti_deposit','1']);
			$this->insertData('web_setting',['name','value'],['manual_linenoti_withdraw','1']);
			$this->insertData('web_setting',['name','value'],['manual_linenoti_report_result','1']);
			$this->insertData('web_setting',['name','value'],['manual_linenoti_other_log','1']);
			$this->insertData('web_setting',['name','value'],['manual_linenoti_register','1']);

			$this->addColumn('log_deposit_withdraw','withdraw_status_request',"TINYINT(1) NULL AFTER created_at");
			$this->addColumn('log_deposit_withdraw','withdraw_status_status',"TINYINT(1) NULL AFTER withdraw_status_request");

			$this->addColumn('finance','bank_withdraw_id',"INT(11) NULL AFTER manage_by");
			$this->addColumn('finance','bank_withdraw_name',"VARCHAR(255) NULL AFTER bank_withdraw_id");

			//Add table for func manage (role,menu,permission) all users
			//user_role 	= ผู้ใช้งาน ผูกกับ สิทธิ์
			/*$this->>migration_service->createTable('user_role',"
				 `id` INT NOT NULL AUTO_INCREMENT ,
				 `user_id` INT(11) NOT NULL ,
				 `role_id` INT(11) NOT NULL ,
				 `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
				 `updated_at` TIMESTAMP NULL ,
				  PRIMARY KEY (`id`)
			");
			$this->>migration_service->addColumnUnique('user_role','UNIQUE','user_id_role_id',"`user_id_role_id` (`user_id`, `role_id`)");
			$this->>migration_service->addColumnConstraint('user_role','CONSTRAINT','user_role_user_id',"`user_role_user_id` FOREIGN KEY (`user_id`) REFERENCES `account`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
			$this->>migration_service->addColumnConstraint('user_role','CONSTRAINT','user_role_role_id',"`user_role_role_id` FOREIGN KEY (`role_id`) REFERENCES `role`(`role_id`) ON DELETE CASCADE ON UPDATE RESTRICT");*/

			//group_menu	= เมนูหลัก
			$this->createTable('group_menu',"
				 `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(150) NOT NULL,
				  `description` varchar(250) DEFAULT NULL,
				  `url` varchar(250) DEFAULT NULL,
				  `icon_class` varchar(150) DEFAULT NULL,
				  `is_deleted` int(1) DEFAULT '0',
				  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  `order` int(11) DEFAULT '0',
				  PRIMARY KEY (`id`)
			","ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8");


			if($this->checkTableExist('group_menu') && $this->checkRowCount('group_menu') === 0){
				$this->insertDataRaw('group_menu','id',1,"",""
					,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (1, 'Dashboard', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 1)");
				$this->insertDataRaw('group_menu','id',2,"",""
					,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (2, 'ระบบสมาชิก', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 2)");
				$this->insertDataRaw('group_menu','id',3,"",""
					,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (3, 'รายงาน', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 3)");
				$this->insertDataRaw('group_menu','id',4,"",""
					,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (4, 'ระบบธุรกรรม', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 4)");
				$this->insertDataRaw('group_menu','id',5,"",""
					,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (5, 'ระบบ LOGS', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 5)");
				$this->insertDataRaw('group_menu','id',6,"",""
					,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (6, 'การตั้งค่า', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 6)");
			}


			//menu 		= เมนูย่อย (parent => group_menu)
			$this->createTable('menu',"
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(100) NOT NULL DEFAULT '0',
				  `description` varchar(150) DEFAULT NULL,
				  `parent_id` int(8) DEFAULT '0',
				  `url` varchar(250) DEFAULT NULL,
				  `icon_class` varchar(150) DEFAULT NULL,
				  `have_child` int(1) DEFAULT '0',
				  `is_deleted` int(1) DEFAULT '0',
				  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  `order` int(11) DEFAULT '0',
				  PRIMARY KEY (`id`)
			","ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8");

			if($this->checkTableExist('menu') && $this->checkRowCount('menu') === 0){
				$this->insertDataRaw('menu','url','home',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (1, 'Dashboard', 'Dashboard', 1, 'home', 'feather icon-home primary', 0, 0,CURRENT_TIMESTAMP, 1)");
				$this->insertDataRaw('menu','url','gamestatus',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (2, 'สถานะเกมส์', 'สถานะเกมส์', 1, 'gamestatus', 'feather icon-crosshair danger', 0, 0,CURRENT_TIMESTAMP, 2)");
				$this->insertDataRaw('menu','url','user',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (3, 'สมาชิก', 'สมาชิก', 2, 'user', 'feather icon-users success', 0, 0,CURRENT_TIMESTAMP, 1)");
				$this->insertDataRaw('menu','url','user_suspend',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (4, 'สมาชิกที่ถูกระงับ', 'สมาชิกที่ถูกระงับ', 2, 'user_suspend', 'feather icon-user-x danger', 0, 0,CURRENT_TIMESTAMP, 2)");
				$this->insertDataRaw('menu','url','agent',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (5, 'พันธมิตร', 'พันธมิตร', 2, 'agent', 'feather icon-users info', 0, 0,CURRENT_TIMESTAMP, 3)");
				$this->insertDataRaw('menu','url','transfer_marketking',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (6, 'โยกสมาชิกการตลาด', 'โยกสมาชิกการตลาด', 2, 'transfer_marketking', 'feather icon-users info', 0, 0,CURRENT_TIMESTAMP, 4)");
				$this->insertDataRaw('menu','url','ref',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (7, 'แนะนำเพื่อน', 'แนะนำเพื่อน', 2, 'ref', 'feather icon-user-plus warning', 0, 0,CURRENT_TIMESTAMP, 5)");
				$this->insertDataRaw('menu','url','ref/bonus',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (8, 'โบนัสแนะนำเพื่อน', 'โบนัสแนะนำเพื่อน', 2, 'ref/bonus', 'feather icon-dollar-sign warning', 0, 0,CURRENT_TIMESTAMP, 6)");
				$this->insertDataRaw('menu','url','bonus/returnbalance',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (9, 'โบนัสคืนยอดเสีย', 'โบนัสคืนยอดเสีย', 2, 'bonus/returnbalance', 'feather icon-dollar-sign warning', 0, 0,CURRENT_TIMESTAMP, 7)");
				$this->insertDataRaw('menu','url','report/business_profit',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (10, 'ผลประกอบการ', 'ผลประกอบการ', 3, 'report/business_profit', 'fa fa-bar-chart success', 0, 0,CURRENT_TIMESTAMP, 1)");
				$this->insertDataRaw('menu','url','report/member_register_sum_deposit',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (11, 'ยอดฝากรวมรายวัน', 'ยอดฝากรวมรายวัน', 3, 'report/member_register_sum_deposit', 'fa fa-bar-chart success', 0, 0,CURRENT_TIMESTAMP, 2)");
				$this->insertDataRaw('menu','url','report/member_not_deposit_less_than_7',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (12, 'ไม่ได้ฝากมากกว่า 7 วัน', 'ไม่ได้ฝากมากกว่า 7 วัน', 3, 'report/member_not_deposit_less_than_7', 'fa fa-bar-chart danger', 0, 0,CURRENT_TIMESTAMP, 3)");
				$this->insertDataRaw('menu','url','report/add_credit',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (13, 'ยอดเติมเครดิต', 'ยอดเติมเครดิต', 3, 'report/add_credit', 'fa fa-bar-chart info', 0, 0,CURRENT_TIMESTAMP, 4)");
				$this->insertDataRaw('menu','url','report/add_bonus',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (14, 'การรับโบนัส', 'การรับโบนัส', 3, 'report/add_bonus', 'fa fa-bar-chart warning', 0, 0,CURRENT_TIMESTAMP, 5)");
				$this->insertDataRaw('menu','url','report/add_promotion',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (15, 'การรับโปรโมชั่น', 'การรับโปรโมชั่น', 3, 'report/add_promotion', 'fa fa-bar-chart primary', 0, 0,CURRENT_TIMESTAMP, 6)");
				$this->insertDataRaw('menu','url','statement',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (16, 'รายการเดินบัญชี', 'รายการเดินบัญชี', 4, 'statement', 'fa fa-bar-chart primary', 0, 0,CURRENT_TIMESTAMP, 1)");
				$this->insertDataRaw('menu','url','deposit',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (17, 'เครดิต', 'เครดิต', 4, 'deposit', 'fa fa-usd warning', 0, 0,CURRENT_TIMESTAMP, 2)");
				$this->insertDataRaw('menu','url','creditwait',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (18, 'เครดิต (รอฝาก)', 'เครดิต (รอฝาก)', 4, 'creditwait', 'feather icon-plus warning', 0, 0,CURRENT_TIMESTAMP, 3)");
				$this->insertDataRaw('menu','url','credit',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (19, 'ฝากเงิน', 'ฝากเงิน', 4, 'credit', 'feather icon-plus primary', 0, 0,CURRENT_TIMESTAMP, 4)");
				$this->insertDataRaw('menu','url','withdraw',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (20, 'ถอนเงิน', 'ถอนเงิน', 4, 'withdraw', 'feather icon-minus danger', 0, 0,CURRENT_TIMESTAMP, 5)");
				$this->insertDataRaw('menu','url','TransferOut',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (21, 'โยกเงินออก', 'โยกเงินออก', 4, 'TransferOut', 'fa fa-money warning', 0, 0,CURRENT_TIMESTAMP, 6)");
				$this->insertDataRaw('menu','url','LogDepositWithdraw',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (22, 'ฝาก-ถอน', 'ฝาก-ถอน', 5, 'LogDepositWithdraw', 'fa fa-history success', 0, 0,CURRENT_TIMESTAMP, 1)");
				$this->insertDataRaw('menu','url','LogAccount',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (23, 'สมาชิก', 'สมาชิก', 5, 'LogAccount', 'fa fa-history primary', 0, 0,CURRENT_TIMESTAMP, 2)");
				$this->insertDataRaw('menu','url','LogReturnBalance',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (24, 'คืนยอดเสีย', 'คืนยอดเสีย', 5, 'LogReturnBalance', 'fa fa-history danger', 0, 0,CURRENT_TIMESTAMP, 3)");
				$this->insertDataRaw('menu','url','LogSms',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (25, 'SMS', 'SMS', 5, 'LogSms', 'fa fa-comment primary', 0, 0,CURRENT_TIMESTAMP, 4)");
				$this->insertDataRaw('menu','url','LogPage',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (26, 'เปิดหน้าเว็ป', 'เปิดหน้าเว็ป', 5, 'LogPage', 'fa fa-history info', 0, 0,CURRENT_TIMESTAMP, 5)");
				$this->insertDataRaw('menu','url','LogLineNotify',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (27, 'Line notify', 'Line notify', 5, 'LogLineNotify', 'fa fa-bell success', 0, 0,CURRENT_TIMESTAMP, 6)");
				$this->insertDataRaw('menu','url','LogWheel',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (28, 'วงล้อ', 'วงล้อ', 5, 'LogWheel', 'fa fa-history warning', 0, 0,CURRENT_TIMESTAMP, 7)");
				$this->insertDataRaw('menu','url','LogTransferOut',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (29, 'โยกเงินออก', 'โยกเงินออก', 5, 'LogTransferOut', 'fa fa-money danger', 0, 0,CURRENT_TIMESTAMP, 8)");
				$this->insertDataRaw('menu','url','promotion',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (30, 'โปรโมชั่น', 'โปรโมชั่น', 6, 'promotion', 'fa fa-clone info', 0, 0,CURRENT_TIMESTAMP, 1)");
				$this->insertDataRaw('menu','url','news',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (31, 'ประกาศ', 'ประกาศ', 6, 'news', 'fa fa-newspaper-o info', 0, 0,CURRENT_TIMESTAMP, 2)");
				$this->insertDataRaw('menu','url','setting/web_setting',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (32, 'ตั้งค่าเว็บ', 'ตั้งค่าเว็บ', 6, 'setting/web_setting', 'fa fa-cog primary', 0, 0,CURRENT_TIMESTAMP, 3)");
				$this->insertDataRaw('menu','url','bank',"",""
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (33, 'ตั้งค่าธนาคาร', 'ตั้งค่าธนาคาร', 6, 'bank', 'fa fa-clone info', 0, 0,CURRENT_TIMESTAMP, 4)");
			}



			//node_menu 	= เมนูย่อย (parent => menu)
			$this->createTable('node_menu',"
				 `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(150) DEFAULT NULL,
				  `description` varchar(250) DEFAULT NULL,
				  `parent_id` int(8) DEFAULT '0',
				  `url` varchar(250) DEFAULT NULL,
				  `icon_class` varchar(150) DEFAULT NULL,
				  PRIMARY KEY (`id`)
			");

			//permission_menu_role (create, update, delete, export, search, view) = การเข้าถึงการจัดการ (สร้าง,แก้ไข,ลบ,ส่งออกรายงาน,ค้นหา,ดูรายการ) ของสิทธิ์ กับ เมนู
			$this->createTable('permission_menu_role',"
				`id` int(11) NOT NULL AUTO_INCREMENT,
				  `role_id` int(11) NOT NULL DEFAULT '0',
				  `menu_id` int(11) NOT NULL DEFAULT '0',
				  `is_create` tinyint(1) NOT NULL DEFAULT '0',
				  `is_update` tinyint(1) NOT NULL DEFAULT '0',
				  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
				  `is_export` tinyint(1) NOT NULL DEFAULT '0',
				  `is_search` tinyint(1) NOT NULL DEFAULT '0',
				  `is_view` tinyint(1) NOT NULL DEFAULT '0',
				  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (`role_id`,`menu_id`),
				  KEY `id` (`id`)
			","ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");
			$this->addColumnConstraint('permission_menu_role','CONSTRAINT','permission_menu_role_menu_id',"`permission_menu_role_menu_id` FOREIGN KEY (`menu_id`) REFERENCES `menu`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
			$this->addColumnConstraint('permission_menu_role','CONSTRAINT','permission_menu_role_role_id',"`permission_menu_role_role_id` FOREIGN KEY (`role_id`) REFERENCES `role`(`role_id`) ON DELETE CASCADE ON UPDATE RESTRICT");

			if($this->checkTableExist('permission_menu_role') && $this->checkRowCount('permission_menu_role') === 0){
				//SuperAdmin
				for($i=1;$i<= 33;$i++){
					$this->insertDataRaw('permission_menu_role','role_id','0',"menu_id",$i
						,"INSERT INTO `permission_menu_role` (`id`, `role_id`, `menu_id`, `is_view`, `is_create`, `is_update`, `is_delete`, `is_export`, `is_search`)
											VALUES (null, '0', $i, 1, 1, 1, 1, 1, 1)");
				}
				//Admin
				$menu_id_list = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,22,23,24,25,26,27,28];
				foreach($menu_id_list as $menu_id){
					$this->insertDataRaw('permission_menu_role','role_id','1',"menu_id",$menu_id
						,"INSERT INTO `permission_menu_role` (`id`, `role_id`, `menu_id`, `is_view`, `is_create`, `is_update`, `is_delete`, `is_export`, `is_search`)
											VALUES (null, '1', $menu_id, 1, 1, 1, 1, 1, 1)");
				}
			}


			//permission_role = การเข้าถึงการจัดการของสิทธิ์นั้นๆ กับ สิทธิ์อื่นๆ เช่น แแอดมินสูงสุดสามารถจัดการผู้ใช้งานได้ทุกสิทธิ์
			$this->createTable('permission_role',"
				`id` int(11) NOT NULL AUTO_INCREMENT,
				  `role_id` int(11) NOT NULL DEFAULT '0',
				  `role_child_id` int(11) NOT NULL DEFAULT '0',
				  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (`role_id`,`role_child_id`),
				  KEY `id` (`id`)
			","ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");
			$this->addColumnConstraint('permission_role','CONSTRAINT','permission_role_role_child_id',"`permission_role_role_child_id` FOREIGN KEY (`role_child_id`) REFERENCES `role`(`role_id`) ON DELETE CASCADE ON UPDATE RESTRICT");
			$this->addColumnConstraint('permission_role','CONSTRAINT','permission_role_role_id',"`permission_role_role_id` FOREIGN KEY (`role_id`) REFERENCES `role`(`role_id`) ON DELETE CASCADE ON UPDATE RESTRICT");

			if($this->checkTableExist('permission_role') && $this->checkRowCount('permission_role') === 0){
				//SuperAdmin
				for($i=0;$i<= 6;$i++){
					$this->insertDataRaw('permission_role','role_id','0',"role_child_id",$i
						,"INSERT INTO `permission_role` (`id`, `role_id`, `role_child_id`)
											VALUES (null, '0', $i)");
				}

				//Admin
				$this->insertDataRaw('permission_role','role_id','1',"role_child_id",'2'
					,"INSERT INTO `permission_role` (`id`, `role_id`, `role_child_id`)
											VALUES (null, '1', 2)");
			}


			//role 	= สิทธิ์
			$this->createTable('role',"
				 `role_id` INT NOT NULL AUTO_INCREMENT , 
				 `role_name` VARCHAR(100) NOT NULL , 
				 `role_level` INT(11) NOT NULL ,
				  PRIMARY KEY (`role_id`)
			");
			$this->addColumnUnique('role','UNIQUE','role_level',"`role_level` (`role_level`)");
			if($this->checkTableExist('role') && $this->checkRowCount('role') === 0){
				$this->insertDataRaw('role','role_id','0',"role_level","0"
					,"INSERT INTO `role` (`role_id`, `role_name`, `role_level`)
											VALUES (0, 'SuperAdmin', '0'");
				$this->insertDataRaw('role','role_id','1',"role_level","1"
					,"INSERT INTO `role` (`role_id`, `role_name`, `role_level`)
											VALUES (1, 'Admin', '1'");
				$this->insertDataRaw('role','role_id','2',"role_level","100"
					,"INSERT INTO `role` (`role_id`, `role_name`, `role_level`)
											VALUES (2, 'User', '100'");
			}


			//เพิ่มเมนูจัดการสำหรับ SuperAdmin
			$this->insertDataRaw('menu','url','role',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (34, 'สิทธิ์การใช้งาน', 'สิทธิ์การใช้งาน', 6, 'role', 'fa fa-cog success', 0, 0,CURRENT_TIMESTAMP, 5)");
			$this->insertDataRaw('menu','url',null,"parent_id","6"
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (35, 'เมนู', 'เมนู', 6, null, 'fa fa-list-ol info', 0, 0,CURRENT_TIMESTAMP, 6)");
			$this->insertDataRaw('node_menu','url','menu/category',"parent_id","35"
				,"INSERT INTO `node_menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`)
											VALUES (1, 'หมวดหมู่', 'หมวดหมู่', 35, 'menu/category', null)");
			$this->insertDataRaw('node_menu','url','menu/main',"parent_id","35"
				,"INSERT INTO `node_menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`)
											VALUES (2, 'เมนูหลัก', 'เมนูหลัก', 35, 'menu/main', null)");
			$this->insertDataRaw('node_menu','url','menu/sub',"parent_id","35"
				,"INSERT INTO `node_menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`)
											VALUES (3, 'เมนูย่อย', 'เมนูย่อย', 35, 'menu/sub', null)");

			$this->insertDataRaw('permission_menu_role','role_id','0',"menu_id",34
				,"INSERT INTO `permission_menu_role` (`id`, `role_id`, `menu_id`, `is_view`, `is_create`, `is_update`, `is_delete`, `is_export`, `is_search`)
											VALUES (null, '0', 34, 1, 1, 1, 1, 1, 1)");
			$this->insertDataRaw('permission_menu_role','role_id','0',"menu_id",35
				,"INSERT INTO `permission_menu_role` (`id`, `role_id`, `menu_id`, `is_view`, `is_create`, `is_update`, `is_delete`, `is_export`, `is_search`)
											VALUES (null, '0', 35, 1, 1, 1, 1, 1, 1)");

			$this->updateDataRaw('menu','url',null,"parent_id",6
				,"UPDATE `menu` SET have_child='1' WHERE url='เมนู' AND parent_id=6");

			if($this->checkTableExist('user_role') && $this->checkRowCount('user_role') >= 1) {
				$this->deleteDataRaw('user_role', "DELETE FROM `user_role` where 1");
			}

			$this->updateDataRaw('menu','url','LogDepositWithdraw',"id","23"
				,"UPDATE `menu` SET url='LogAccount' WHERE url='LogDepositWithdraw' AND parent_id=5 AND id=23");

			$this->updateDataRaw('menu','url','transfer_marketking',"id","6"
				,"UPDATE `menu` SET url='Transfer_marketing' WHERE url='transfer_marketking' AND parent_id=2 AND id=6");

			$this->addColumn('group_menu','is_deleted'," TINYINT(1) NOT NULL DEFAULT '0'");
			$this->addColumn('role','is_deleted',"TINYINT(1) NOT NULL DEFAULT 0");
			$this->addColumn('log_page','role',"INT(5) NULL DEFAULT NULL");
			$this->addColumn('node_menu','is_deleted'," TINYINT(1) NOT NULL DEFAULT '0'");
			$this->addColumnRaw('node_menu','order','`order`'," INT(5) NOT NULL DEFAULT 0");

			$this->updateDataRaw('node_menu','url','menu/category',"id","1"
				,"UPDATE `node_menu` SET `order`='1' WHERE url='menu/category' AND parent_id=35 AND id=1");
			$this->updateDataRaw('node_menu','url','menu/main',"id","2"
				,"UPDATE `node_menu` SET `order`='2' WHERE url='menu/main' AND parent_id=35 AND id=2");
			$this->updateDataRaw('node_menu','url','menu/sub',"id","3"
				,"UPDATE `node_menu` SET `order`='3' WHERE url='menu/sub' AND parent_id=35 AND id=3");

			$this->updateDataRaw('menu','id','35',"",""
				,"UPDATE `menu` SET `have_child`='1' WHERE id=35");

			// Format ขั้นตอนการตั้งชื่อ controller และชื่อ method ต่างๆที่อยู่ภายใต้ controller นั้น **จุดประสงค์เพื่อไม่ให้มีปัญหากับระบบ filter menu,role
			/*
			   เช่นจะเพิ่มเมนูที่ชื่อว่า "ดำเนินการ" เข้าผ่าน url : /admin/operator
			   ชื่อ Controller ควรตั้งว่า Operator
			   ชื่อ Method ต่างๆที่อยู่ภายใต้ Controller นั้นควรขึ้นต้นด้วย operator_[ชื่อ method] เช่น
			      - operator_list_page
			      - operator_form_update
			      - operator_update
			      - operator_form_create
			      - operator_create
			*/

			/*

			  //หากต้องการเพิ่มเมนูใหม่ ให้ดำเนินการ template นี้

			  parent_id คือ table group_menu
					- Dashboard 	=> id = 1
					- ระบบสมาชิก		=> id = 2
					- รายงาน		=> id = 3
					- ระบบธุรกรรม	=> id = 4
					- ระบบ LOGS		=> id = 5
					- การตั้งค่า		=> id = 6

				$params = [
					'id' => 36,	 			//id ล่าสุดของ table menu คือ 35 อ้างอิงจากค้นหาในไฟล์ migrate นี้คำว่า "INSERT INTO `menu` (`id`, `name`, `description`" หากมีการเพิ่มใหม่ให้บวกเพิ่ม +1
					'parent_id' => null,	//เลือกได้จากด้านบน parent_id [1-6] เท่านั้น
					'url' => 'url_menu',	// url_menu => /admin/url_menu หากเมนูนี้ต้องการมี node_menu ให้ใส่เป็น null และค่า have_child=1
					'have_child' => 0,		//1=เมนูนี้จะมี node menu ภายใต้, 0 = ไม่มี node menu
					'name' => 'ชื่อเมนู',
					'description' => 'รายละเอียดเมนู',
					'icon_class' => 'fa fa-cog success',
					'is_deleted' => '0',
					'order' => '0',			//เรียงลำดับ
				];
				$this->insertDataRaw('menu','url',$params['url'],"parent_id",$params['parent_id']
					,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
												VALUES ('".$params["id"]."', '".$params["name"]."', '".$params["description"]."',  '".$params["parent_id"]."' , '".$params["url"]."','".$params["icon_class"]."',".$params["have_child"].", ".$params["is_deleted"].",CURRENT_TIMESTAMP, ".$params["order"].")");

			*/

			/*

			  //หากต้องการเพิ่ม node menu ภายใต้เมนู ให้ดำเนินการ template นี้

				$params_node_menu = [
					'id' => null,
					'parent_id' => 36,			//id ของ table menu ที่ node menu นี้จะอยู่ภายใต้
					'url' => 'url_menu/url_node_menu',	// url_menu => /admin/url_menu/url_node_menu
					'name' => 'ชื่อเมนูย่อย',
					'description' => 'รายละเอียดเมนูย่อย',
					'icon_class' => '',
					'is_deleted' => '0',
					'order' => '0',				//เรียงลำดับ
				];
				$this->insertDataRaw('node_menu','url',$params_node_menu['url'],"parent_id",$params_node_menu['parent_id']
					,"INSERT INTO `node_menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `is_deleted`, `order`)
												VALUES (".$params_node_menu['id'].", '".$params_node_menu['name']."', '".$params_node_menu['description']."', ".$params_node_menu['parent_id'].", '".$params_node_menu['url']."', '".$params_node_menu['icon_class']."', ".$params_node_menu["is_deleted"].", ".$params_node_menu["order"].")");
			*/

			$this->addColumn('promotion','description'," VARCHAR(1000) NULL");
			$this->insertDataRaw('web_setting','name','line_messages_webhook',"",""
				,"INSERT INTO `web_setting` (`id`, `name`, `value`) VALUES (NULL, 'line_messages_webhook', '')");

			$this->insertDataRaw('web_setting','name','line_send_messages_status',"",""
				,"INSERT INTO `web_setting` (`id`, `name`, `value`) VALUES (NULL, 'line_send_messages_status', '0')");

			$this->insertDataRaw('web_setting','name','line_messages_token',"",""
				,"INSERT INTO `web_setting` (`id`, `name`, `value`) VALUES (NULL, 'line_messages_token', '')");

			//INSERT INTO `banks`(`bank_name`,`bank_code`,`code_en`)VALUES('True Wallet','999','TMN');
			$this->insertDataRaw('banks','bank_code','999',"",""
				,"INSERT INTO `banks`(`bank_name`,`bank_code`,`code_en`)VALUES('True Wallet','999','TMN')");

			//ALTER TABLE `bank` ADD `check_regis` INT(1) NOT NULL DEFAULT '0' AFTER `auto_min_amount_transfer`;
			$this->addColumn('bank','check_regis'," INT(1) NOT NULL DEFAULT '0'");

			$this->insertDataRaw('web_setting','name','minimum_com',"",""
				,"INSERT INTO web_setting (id, name, value) VALUES (NULL, 'minimum_com', '0');");

			$this->insertDataRaw('web_setting','name','withdraw_all_status',"",""
				,"INSERT INTO web_setting (id, name, value) VALUES (NULL, 'withdraw_all_status', '0');");
		}
	}
}
