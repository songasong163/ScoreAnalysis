<?php
namespace app\controller;
use PHPExcel_IOFactory;
use app\BaseController;
use Symfony\Component\VarDumper\Caster\UuidCaster;
use think\facade\View;
use think\facade\Db;

class Index extends BaseController
{
    public function index()
    {
        return View::fetch('index');
    }

    public function screen()
    {
        return View::fetch('screen');
    }

    public function Analysis(){
        
        $file = request()->file('file');
        
        $savename = \think\facade\Filesystem::putFile( 'topic', $file);
        
        $objPHPExcel = PHPExcel_IOFactory::load(runtime_path().'storage\\'.$savename);//获取sheet表格数目
        
        //$objPHPExcel = PHPExcel_IOFactory::load(runtime_path().'storage\\topic\\20221120\\83fc4886222edfad66ff02084928cc42.xlsx');
        $sheetCount = $objPHPExcel->getSheetCount();
        $sheetSelected = 0;//默认选中sheet0表
        $objPHPExcel->setActiveSheetIndex($sheetSelected);
        $rowCount = $objPHPExcel->getActiveSheet()->getHighestRow();//获取行数
        $columnCount = $objPHPExcel->getActiveSheet()->getHighestColumn();//获取列数
        $dataArr = array();
        $dataTitle = array();
        $uuid = md5(uniqid());
        cookie('uuid', $uuid, 3600);
        
        //Excel标题行
        for($col=ord('A');$col<=ord($columnCount);$col++)
            $dataTitle[$col-64]=$objPHPExcel->getActiveSheet()->getCell(chr($col).'1')->getValue();
          
        //Excel标题行转数据库字段
        for($col=ord('A');$col<=ord($columnCount);$col++)
            switch($dataTitle[$col-64]){
                case "姓名":$dataTitle[$col-64]='name';break;
                case "语文":$dataTitle[$col-64]='chinese';break;
                case "数学":$dataTitle[$col-64]='math';break;
                case "英语":$dataTitle[$col-64]='english';break;
                case "物理":$dataTitle[$col-64]='physics';break;
                case "化学":$dataTitle[$col-64]='chemistry';break;
                case "生物":$dataTitle[$col-64]='biology';break;
                case "历史":$dataTitle[$col-64]='history';break;
                case "政治":$dataTitle[$col-64]='politics';break;
                case "地理":$dataTitle[$col-64]='geography';break;
                case "综合":$dataTitle[$col-64]='comprehensive';break;
            }
        
        //判断上传的Excel中是否有综合成绩列
        $is_comprehensive=in_array("comprehensive",$dataTitle);
        //导入文件中没有综合列时添加综合列
        if(!$is_comprehensive)
            $dataTitle[count($dataTitle)+1]="comprehensive";   
        
        //将excel的数据转换成入库需要的数组
        for($row = 2;$row<=$rowCount;$row++){
            $dataArr[$row-1][$dataTitle[count($dataTitle)]]=0;
            for($col=ord('A');$col<=ord($columnCount);$col++){
                $dataArr[$row-1][$dataTitle[$col-64]]=$objPHPExcel->getActiveSheet()->getCell(chr($col).$row)->getValue();
                //$dataArr[$row-1][$col-64]=$objPHPExcel->getActiveSheet()->getCell(chr($col).$row)->getValue();
                if(!$is_comprehensive && $dataTitle[$col-64]!="name")
                    $dataArr[$row-1]['comprehensive']+=$dataArr[$row-1][$dataTitle[$col-64]];
                $dataArr[$row-1]['uuid']=$uuid;
                
            }
        }
        
        //将数据逐行导入数据库
        $database_name='main';
        for($row = 1;$row<=$rowCount-1;$row++)
            $insert = Db::name($database_name)->insert($dataArr[$row]);
        
        return json_encode($uuid);
    }
}
