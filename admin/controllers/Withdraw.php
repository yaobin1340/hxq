<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Withdraw extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('withdraw_model');
	}

	public function list_withdraw($status,$page=1){

		$data = $this->withdraw_model->list_withdraw($page,$status);
		$base_url = "/admin.php/withdraw/list_withdraw/".$status;
		$pager = $this->pagination->getPageLink_by4($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->assign('status', $status);
		$this->assign('page', $page);
		$this->show('withdraw/list_withdraw');
	}


	public function withdraw_detail($id,$status=1,$page=1){
		$data = $this->withdraw_model->withdraw_detail($id);
		$this->assign('status', $status);
		$this->assign('page', $page);
		$this->assign('data', $data);
		$this->show('withdraw/audit_withdraw');
	}

	public function audit_withdraw(){
		$rs = $this->withdraw_model->save_audit_withdraw();
		if($rs == 1){
			$this->show_message('审核成功',site_url('withdraw/list_withdraw/1/1'));
		}else{
			$this->show_message('审核失败');
		}
	}
	public function down_excel(){
		$data_res = $this->withdraw_model->down_excel();
		if(!$data_res){
			$this->show_message('没有需要导出的数据');
		}
		$project_name = '名称';
		require_once (APPPATH . 'libraries/PHPExcel/PHPExcel.php');
		$excel  = new \PHPExcel ();
		switch ($this->input->post('type')){
			case 1:
				$main_name ='待审核';
				break;
			case 2:
				$main_name ='审核通过';
				break;
			case -1:
				$main_name ='审核拒绝';
				break;
			default:
				$main_name ='';
				break;
		}
		$excel->getActiveSheet()->setCellValue("A1","三客柚 提现报表(".$main_name.")");
		$excel->getActiveSheet()->mergeCells('A1:F1');
		$excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$excel_name = "申请时间:".($this->input->post('s_date')?$this->input->post('s_date'):"最早")."——".($this->input->post('e_date')?$this->input->post('e_date'):"至今");
		$excel->getActiveSheet()->setCellValue("A2",$excel_name);
		$excel->getActiveSheet()->mergeCells('A2:F2');

		$letter = array('A','B','C','D','E','F');
		$tableheader = array('序','银行','账号','户主','金额','提现编号');
		for($i = 0;$i < count($tableheader);$i++) {
			$excel->getActiveSheet()->setCellValue("$letter[$i]3","$tableheader[$i]");
		}
		$data = array();

		foreach ($data_res as $k=>$v){
			$sname = '123';
			$data[] = array(($k+1),$v['bank'],$v['bank_no'],$v['rel_name'],($v['money']-$v['sxf'])/100,$v['id']);
		}

		for ($i = 4;$i <= count($data) + 3;$i++) {
			$j = 0;
			foreach ($data[$i - 4] as $key=>$value) {
				if($key==2){
					$excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
				}else{
					$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
				}

				$j++;
			}
		}
		$write = new \PHPExcel_Writer_Excel5 ($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");;
		header('Content-Disposition:attachment;filename="'.'提现报表'.date('Y-m-d H:i:s',time()).'.xls"');
		header("Content-Transfer-Encoding:binary");
		$write->save('php://output');
	}
}