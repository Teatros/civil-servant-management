<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\statistic;

use app\api\model\DocumentUploadRecord as DocumentUploadRecordModel;
use app\api\model\LoginRecord as LoginRecordModel;
use DateTime;
use think\Request;


class Statistic
{
    public function getDocumentUploadStatistics(Request $request)
    {
        $records = DocumentUploadRecordModel::select();
        $groupedData = array_reduce($records->toArray(), function ($result, $item) {
            $key = $item['dept_name'];
            if (!array_key_exists($key, $result)) {
                $result[$key] = [];
            }
            $result[$key][] = $item;
            return $result;
        }, []);
        $returnData = [];
        foreach ($groupedData as $k => $v){
            $size = sizeof($v);
            $data = ['value'=>$size,'name'=>$k];
            array_push($returnData,$data);
        }
        return writeJson(200, $returnData, '获取成功');
    }

    public function getDailyDocumentUploadStatistics(Request $request)
    {
        $records = DocumentUploadRecordModel::query('
        select DATE_FORMAT(upload_date_time,"%Y-%m-%d") as date,count(1) as value
        from document_upload_record
        WHERE upload_date_time > SUBDATE(now(),7) and upload_date_time<ADDDATE(now(),1)
        GROUP BY DATE_FORMAT(upload_date_time,"%Y-%m-%d")
        ORDER BY DATE_FORMAT(upload_date_time,"%Y-%m-%d") DESC
        ');
        $map = [];
        foreach ($records as $item) {
            $map[$item['date']] = $item['value'];
        }
        $values = [];
        $currentDate = new DateTime();
        $timeList = array();
        for ($i = 1; $i <= 6; $i++) {
            $date = $currentDate->modify('-1 day')->format('Y-m-d');
            $timeList[] = $date;
            $value = $map[$date];
            if(isset($value)){
                array_push($values,$value);
            }else{
                array_push($values,0);
            }
        }

        $returnData = ['dates'=>array_reverse($timeList),'values'=>array_reverse($values)];
        return writeJson(200, $returnData, '获取成功');
    }

    public function listLoginRecord(Request $request)
    {
        $params = $request->post();

        $list = LoginRecordModel::query('
            select b.name as user_name,c.dept_name,d.name as role_name,a.login_date_time 
            from login_record a
            left join sys_user b on a.sys_user_id = b.id
            left join sys_dept c on b.dept_id = c.dept_id
            left join sys_role d on b.role_id = d.id
            ORDER BY a.login_date_time  DESC limit 20
        ');

        return writeJson(200, $list, '获取成功');
    }
}