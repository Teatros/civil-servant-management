<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\file;

use app\api\model\SysUser as SysUserModel;
use app\api\model\DocumentUploadRecord as DocumentUploadRecordModel;
use app\api\model\User as UserModel;
use app\api\model\Dept as DeptModel;
use PHPExcel_IOFactory;
use app\api\service\token\LoginToken;
use app\lib\util\UUIDUtil;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Hook;
use think\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;

class FileImport
{

    private $save_path = '/Users/tangqi/Desktop/';
    private $loginTokenService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws \Exception
     */
    public function importUserInfo(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();

        $file = request()->file('file');
        $reader = PHPExcel_IOFactory::createReaderForFile($file->getRealPath());
        $filename = $file->getInfo()['name'];
        $obj_PHPExcel = $reader->load($file->getRealPath());
        $data_list = [];
        $excel_array = $obj_PHPExcel->getsheet(0)->toArray();
        foreach ($excel_array as $k => $v) {
            if($k == 0){
                continue;
            }
            $name = $v[0];
            $id_card_no = $v[1];
            $gender = $v[2];
            $birth_date = $v[3];
            $nation = $v[4];
            $political_status = $v[5];
            $work_unit = $v[6];
            $dept_name= $v[7];
            $position = $v[8];
            $start_date_time = $v[9];
            $db_user = UserModel::where('id_card_no','=',$id_card_no)->select();
            if(sizeof($db_user)>0){
                continue;
            }
            $user=['id'=>UUIDUtil::getUUID(),'name'=>$name,'id_card_no'=>$id_card_no,'gender'=>$gender,'birth_date'=>$birth_date,
                  'nation'=>$nation,'political_status'=>$political_status,'work_unit'=>$work_unit,
                  'position'=>$position,'hold_start_date'=>$start_date_time];
            $dept = DeptModel::where('dept_name','=',$dept_name)->find();
            if(!is_null($dept)){
                $user['dept_id'] = $dept['dept_id'];
            }
            $user['create_user_id'] = $uid;
            $user['create_date_time'] = date('Y-m-d H:i:s');
            $user['update_user_id'] = $uid;
            $user['update_date_time'] = date('Y-m-d H:i:s');
            UserModel::create($user);
            array_push($data_list,$user);
        }

        if(sizeof($data_list)>0){
            $sys_user = SysUserModel::get($uid)
                ->with([
                    'dept'=>function ($dept) {
                        $dept->field('dept_id,dept_name');
                    },

                ])->find();
            $upload = ['sys_user_id'=>$uid,'file_name'=>$filename,'dept_name'=>$sys_user['dept']['dept_name'],'upload_date_time'=>date('Y-m-d H:i:s')];
            DocumentUploadRecordModel::create($upload);
        }
        return writeJson(200, '', '导入成功');
    }
}