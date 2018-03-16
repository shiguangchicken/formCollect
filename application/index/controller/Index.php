<?php
namespace app\index\controller;
use PHPExcel_IOFactory;
use PHPExcel;
use think\Controller;

class Index extends Controller
{

    //excel表单,录入信息
    public function index(){
        $filename='/public/upload/table.xlsx';
        $data=$this->read_excelinfo($filename);
        return view('index',['data'=>$data]);
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
        $PHPExcelObj->getActiveSheet()->fromArray($head[0],NULL,'A1');
        $PHPExcelObj->getActiveSheet()->fromArray($array,NULL,'A2');
        $PHPExcelObj->getActiveSheet()->setTitle("统计表格");

        $ObjWriter=PHPExcel_IOFactory::createWriter($PHPExcelObj,'Excel2007');
        $ObjWriter->save('/public/upload/tjb.xlsx');

        $this->success('提交成功，无需重复提交','_404');
    }

    public function _404(){
        return view();
}

}
