<?php
namespace app\controller;
use app\BaseController;
use think\facade\Db;
use think\facade\Request;

class Dataset extends BaseController {
   //仪表盘
    public function getPanelQualify(){
        $get_uuid = input('post.')['uuid'];
        if(is_null($get_uuid))
            $get_uuid = cookie('uuid');
        $subject = input('post.')['subject'];
        $qualify_score = input('post.')['qualify_score'];
        $sql_query = "select (sum(if((".$subject." >= ".$qualify_score."),1,NULL)) / sum(1)) AS `panelqualify1` from `main`  where uuid ='".$get_uuid."'";
        
        $result = Db::query($sql_query);
        return $result;
    }
    //指标卡
    public function threeTarget(){
        $get_uuid = input('post.')['uuid'];
        if(is_null($get_uuid))
            $get_uuid = cookie('uuid');
        $subject = input('post.')['subject'];
        $sql_query = "select max(".$subject.") as max_score,min(".$subject.") as min_score,avg(".$subject.") as avg_score from `main`";
        return $this->sqlOptions($sql_query);
    }
    //排行榜   
    public function scoreTop(){
        $get_uuid = input('post.')['uuid'];
        if(is_null($get_uuid))
            $get_uuid = cookie('uuid');
        $subject = input('post.')['subject'];
        $sql_query = "select name,".$subject." as subject_name from `main` where uuid ='".$get_uuid."' order by ".$subject." desc limit 10";
        $result = Db::query($sql_query);
        return $result;
    }
    //科目成绩明细
    public function scoreDelta(){
        $get_uuid = input('post.')['uuid'];
        if(is_null($get_uuid))
            $get_uuid = cookie('uuid');
        $subject = input('post.')['subject'];
        $sql_query = "select name,".$subject." as score from main where uuid = '".$get_uuid."'";
        $result = Db::query($sql_query);
        
        return $result;
    }
    //差异占比
    public function diffrent(){
        $get_uuid = input('post.')['uuid'];
        if(is_null($get_uuid))
            $get_uuid = cookie('uuid');
        $excellent = input('post.')['excellent'];
        $fine = input('post.')['fine'];
        $badly = input('post.')['badly'];
        $subject = input('post.')['subject'];
        $sql_query1 = "select '优秀' as typed, count(if(".$subject.">=".$excellent.",1,null)) as val from `main` where uuid = '".$get_uuid."'";
        $sql_query2 = " union all select '良好' as typed, count(if(".$subject.">=".$fine." && ".$subject."<".$excellent.",1,null)) as val from `main` where uuid = '".$get_uuid."'";
        $sql_query3 = " union all select '落后' as typed, count(if(".$subject."<".$badly.",1,null)) as val from `main` where uuid = '".$get_uuid."'";
        $sql_query = $sql_query1.$sql_query2.$sql_query3;
        
        $result = Db::query($sql_query);
        
        return $result;
    }
    //成绩明细
    public function allScoreDelta(){
        $get_uuid = input('post.')['uuid'];
        if(is_null($get_uuid))
            $get_uuid = cookie('uuid');
        $sql_query = "select name as `姓名`,chinese as `语文`,math as `数学`,english as `英语`,physics as `物理`,chemistry as `化学`,biology as `生物`,history as `历史`,politics as `政治`,geography as `地理`,comprehensive as `综合` from `main`  where uuid = '".$get_uuid."'";
        $result = Db::query($sql_query);
        return $result;
    }
	public function allSub(){
		$get_uuid = input('post.')['uuid'];
        if(is_null($get_uuid))
            $get_uuid = cookie('uuid');
        $sql_query = "select sum(chinese) chinese,sum(math) math,sum(english) english,sum(physics) physics,sum(chemistry) chemistry,sum(biology) biology,sum(history) history,sum(politics) politics,sum(geography) geography from `main` where uuid = '".$get_uuid."'";
        $result = Db::query($sql_query);
        return $result;
	}

    //公共出口
    public function sqlOptions($sql_query){
        
        $get_uuid = input('post.')['uuid'];
        if(is_null($get_uuid))
            $get_uuid = cookie('uuid');
        
        $final_query = $sql_query." where uuid ='".$get_uuid."'";
        $result = Db::query($final_query);
        return $result;
        
    }


    /*
        个人成绩独立分析接口

    */
    //个人指标卡
    public function IndicatorCard(){
        $uuid = input('post.')['uuid'];
        $sName = input('post.')['name'];
        $chineseQscore = input('post.')['chineseQscore'];
        $mathQscore = input('post.')['mathQscore'];
        $englishQscore = input('post.')['englishQscore'];
        $physicQscore = input('post.')['physicQscore'];
        $chemistryQscore = input('post.')['chemistryQscore'];
        $biologyQscore = input('post.')['biologyQscore'];
        $historyQscore = input('post.')['historyQscore'];
        $politicsQscore = input('post.')['politicsQscore'];
        $geographyQscore = input('post.')['geographyQscore'];
		$sql_query="
        select (chinese+math+english+physics+chemistry+biology+history+politics+geography) as unquaSub,comprehensive,compSort
        from (
            select
            name,
            IFNULL(if(chinese<".$chineseQscore.",1,0),0) as chinese,
            IFNULL(if(math<".$mathQscore.",1,0),0) as math,
            IFNULL(if(english<".$englishQscore.",1,0),0) as english,
            IFNULL(if(physics<".$physicQscore.",1,0),0) as physics,
            IFNULL(if(chemistry<".$chemistryQscore.",1,0),0) as chemistry,
            IFNULL(if(biology<".$biologyQscore.",1,0),0) as biology,
            IFNULL(if(history<".$historyQscore.",1,0),0) as history,
            IFNULL(if(politics<".$politicsQscore.",1,0),0) as politics,
            IFNULL(if(geography<".$geographyQscore.",1,0),0) as geography,
            comprehensive,
            ROW_NUMBER() OVER (ORDER BY comprehensive DESC) compSort
            from main 
            where uuid='".$uuid."'
        ) TEMP
        where name='".$sName."'";
        $result = Db::query($sql_query);
        return $result;

    }
    //个人成绩各科占比
    public function ratio(){
        $uuid = input('post.')['uuid'];
        $sName = input('post.')['name'];
        $sql_query="
        select subject,score from (
            select uuid,name,'语文' subject,chinese score from main 
            union all
            select uuid,name,'数学' subject,math score from main
            union all
            select uuid,name,'英语' subject,english score from main
            union all
            select uuid,name,'物理' subject,physics score from main
            union all
            select uuid,name,'化学' subject,chemistry score from main
            union all
            select uuid,name,'生物' subject,biology score from main
            union all
            select uuid,name,'历史' subject,history score from main
            union all
            select uuid,name,'政治' subject,politics score from main
            union all
            select uuid,name,'地理' subject,geography score from main
            ) T
            where uuid = '".$uuid."' and name = '".$sName."'
            and score is not null
            ";
            $result = Db::query($sql_query);
            return $result;
    }
    //雷达图
    public function radar(){
        $uuid = input('post.')['uuid'];
        $sName = input('post.')['name'];
        $sql_query="select chinese 语文,math 数学,english 英语,physics 物理,chemistry 化学,biology 生物,history 历史,politics 政治,geography 地理 from main
        where uuid = '".$uuid."' 
        and name = '".$sName."'";
        $result = Db::query($sql_query);
        return $result;
    }
    //个人成绩明细
    public function allScore(){
        $uuid = input('post.')['uuid'];
        $sName = input('post.')['name'];
        $sql_query="
		with temp as(select * from main where uuid = '".$uuid."')
        select subject 科目,score 现成绩,sort 现排名 from (
            select uuid,name,'综合' subject,comprehensive score,ROW_NUMBER() OVER (ORDER BY comprehensive DESC) sort from temp
            union all
            select uuid,name,'语文' subject,chinese score,ROW_NUMBER() OVER (ORDER BY chinese DESC) sort from temp 
            union all
            select uuid,name,'数学' subject,math score,ROW_NUMBER() OVER (ORDER BY math DESC) sort from temp
            union all
            select uuid,name,'英语' subject,english score,ROW_NUMBER() OVER (ORDER BY english DESC) sort from temp
            union all
            select uuid,name,'物理' subject,physics score,ROW_NUMBER() OVER (ORDER BY physics DESC) sort from temp
            union all
            select uuid,name,'化学' subject,chemistry score,ROW_NUMBER() OVER (ORDER BY chemistry DESC) sort from temp
            union all
            select uuid,name,'生物' subject,biology score,ROW_NUMBER() OVER (ORDER BY biology DESC) sort from temp
            union all
            select uuid,name,'历史' subject,history score,ROW_NUMBER() OVER (ORDER BY history DESC) sort from temp
            union all
            select uuid,name,'政治' subject,politics score,ROW_NUMBER() OVER (ORDER BY politics DESC) sort from temp
            union all
            select uuid,name,'地理' subject,geography score,ROW_NUMBER() OVER (ORDER BY geography DESC) sort from temp
            ) T
            where name = '".$sName."'
            and score is not null
            ";
            $result = Db::query($sql_query);
            return $result;
            
    }
    //综合成绩
    public function sumScore(){
        $uuid = input('post.')['uuid'];
        $sName = input('post.')['name'];
        $sql_query="select comprehensive from main where uuid ='".$uuid."' and name = '".$sName."'";
        $result = Db::query($sql_query);
        return $result;
    }
    //对比雷达图
    public function constradar(){
        $uuid = input('post.')['uuid'];
        $sName = input('post.')['name'];
        $cName = input('post.')['conname'];
        $sql_query="select chinese 语文,math 数学,english 英语,physics 物理,chemistry 化学,biology 生物,history 历史,politics 政治,geography 地理,name from main
        where uuid = '".$uuid."' 
        and (name = '".$sName."' or name = '".$cName."') order by FIELD(name,'".$cName."')";
        $result = Db::query($sql_query);
        return $result;
    }
    //横向对比图
    public function broadwise(){
        $uuid = input('post.')['uuid'];
        $sName = input('post.')['name'];
        $cName = input('post.')['conname'];
         $sql_query="
        select name,subject,score from (
            select uuid,name,'语文' subject,chinese score from main 
            union all
            select uuid,name,'数学' subject,math score from main
            union all
            select uuid,name,'英语' subject,english score from main
            union all
            select uuid,name,'物理' subject,physics score from main
            union all
            select uuid,name,'化学' subject,chemistry score from main
            union all
            select uuid,name,'生物' subject,biology score from main
            union all
            select uuid,name,'历史' subject,history score from main
            union all
            select uuid,name,'政治' subject,politics score from main
            union all
            select uuid,name,'地理' subject,geography score from main
            ) T
            where uuid = '".$uuid."' and (name = '".$sName."' or name = '".$cName."')
            and score is not null
            ";
            $result = Db::query($sql_query);
            return $result;

    }
	//对比学生名单
	public function allName(){
        $uuid = input('post.')['uuid'];
        $sName = input('post.')['sname'];
      
        $sql_query="select name from main where uuid = '".$uuid."' and name != '".$sName."'";
		$result = Db::query($sql_query);
		return $result;
    }
}