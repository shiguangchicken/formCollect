<?php
namespace app\index\controller;
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
    //excel表单,录入信息
    public function add_student_info(){
        $filename='/public/upload/table.xlsx';
        $data=$this->read_excelinfo($filename);
        return view('add_student_info',['data'=>$data]);
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
//将表单提交的信息写入txt中
    public function save_to_excel(){
        //$head=$this->read_excelinfo();//读取表头信息
        //将信息写入txt文本
       $txt=fopen('/public/upload/temp_info.txt','a+');
        $data=input();
        foreach ($data as $w){
            fwrite($txt,$w);
            fwrite($txt,"  ");
        }
        fwrite($txt,"\r\n");
        fclose($txt);

        $txt=fopen('/public/upload/temp_info.txt','r');
        $array=[];
        while (($data=fgets($txt))!=false){
            $array1=explode('  ',$data);
            array_push($array,$array1);
        }
        //var_dump($array);
        fclose($txt);
        $head=$this->read_excelinfo('/public/upload/table.xlsx');//读取表头信息
        $PHPExcelObj=new PHPExcel();
        $PHPExcelObj->getProperties()->setCreator("fang")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("表格信息")
            ->setSubject("PHPExcel Test Document")
            ->setDescription("Test document for PHPExcel, generated using PHP classes.")
            ->setKeywords("office PHPExcel php")
            ->setCategory("Test result file");
        $PHPExcelObj->getActiveSheet()->fromArray($head,NULL,'A1');
        $PHPExcelObj->getActiveSheet()->fromArray($array,NULL,'A2');
        $PHPExcelObj->getActiveSheet()->setTitle("统计表格");

        $ObjWriter=PHPExcel_IOFactory::createWriter($PHPExcelObj,'Excel2007');
        $ObjWriter->save('/public/upload/tjb.xlsx');

        $this->success('提交成功','add_student_info');
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
    //后台上传excel文件

    public function clear_txt(){
        //清除原来的txt文本
        if (file_exists('/public/upload/temp_info.txt')){
            unlink('/public/upload/temp_info.txt');
            unlink('/public/upload/tjb.xlsx');
        }
        $this->redirect('index');
    }
}
