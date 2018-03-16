<?php
/**
 * Created by PhpStorm.
 * User: fang
 * Date: 2018/3/12
 * Time: 13:22
 */

namespace app\admin\controller;
use PHPExcel_IOFactory;
use PHPExcel;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        if(file_exists('/public/upload/tjb.xlsx')){
            $body=$this->read_excelinfo('/public/upload/tjb.xlsx');
            $table_color=['active','success','warning','danger','info'];
            //dump($body);
            return view('index',['data'=>$body,'color'=>$table_color]);
        }else{
            return view('admin_up',['mesg'=>"当前没有人提交信息"]);
        }

    }

    //读取excel表格
    public function read_excelinfo($filenae){
        $arry=[];
        $PHPExcel=PHPExcel_IOFactory::load($filenae);
        $sheet=$PHPExcel->getActiveSheet();
        foreach ($sheet->getRowIterator() as $row){
            $arr1=[];
            $cellIterrator=$row->getCellIterator();
            foreach ($cellIterrator as $cell){
                array_push($arr1,$cell->getValue());
            }
            array_push($arry,$arr1);
        }
        //print_r($arry);
        return $arry;
    }




    //管理员下载excel文件
    public function download_excel(){
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="tjb.xlsx"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        readfile('/public/upload/tjb.xlsx');
    }

    //后台上传界面
    public function admin_up(){
        return view('',['mesg'=>"当前没有人提交信息"]);
    }
    public function up_excel(){
        $oldname= $_FILES['excel']['name'];
        $suffix=strstr($oldname,'.');
        $temp_name=$_FILES['excel']['tmp_name'];
        $error=$_FILES['excel']['error'];
        if ($error>0){
            die('error uploading file');
        }else{
            if ($suffix=='.xls'||$suffix=='.xlsx'){
                move_uploaded_file($temp_name,'/public/upload/table.xlsx');
                $this->success('上传文件成功，请勿重复上传');
            }else{
                echo "上传文件不是excel文件";
            }
        }
    }


    public function clear_txt(){
        //清除原来的txt文本
        if (file_exists('/public/upload/temp_info.txt')){
            unlink('/public/upload/temp_info.txt');
            unlink('/public/upload/tjb.xlsx');
        }
        $this->redirect('index');
    }

}