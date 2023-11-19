<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\file;

use app\api\model\SysUser as SysUserModel;
use app\api\service\token\LoginToken;
use app\lib\util\UUIDUtil;
use app\api\model\User as UserModel;
use app\api\model\honestDocument\Dishonesty as DishonestyModel;
use app\api\model\honestDocument\AnnualEvaluation as AnnualEvaluationModel;
use app\api\model\honestDocument\RewardPunishment as RewardPunishmentModel;
use app\api\model\honestDocument\SocialCredit as SocialCreditModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Hook;
use think\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use think\Response;

class FileExport
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
     * @throws DataNotFoundException
     * @throws \Exception
     */
    public function exportUserInfo(Request $request)
    {
        $params = $request->get();
        $uid = $params['id'];
        $sys_user_id = $this->loginTokenService->getCurrentUid();

        $document = new TemplateProcessor('template.docx');
        $user = UserModel::get($uid);
        $document -> setValue('name', $user['name']);
        $document -> setValue('gender', $user['gender']);
        $document -> setValue('birth_date', $user['birth_date']);
        $document -> setValue('nation', $user['nation']);
        $document -> setValue('political_status', $user['political_status']);
        $document -> setValue('id_card_no', $user['id_card_no']);

        $where = [
            ['user_id','=',$uid],
            ['is_delete','=',0],
        ];
        $dishonestyDatas = DishonestyModel::where($where)->select();
        $groupedData1 = array_reduce($dishonestyDatas->toArray(), function ($result, $item) {
            $key = $item['type'];
            if (!array_key_exists($key, $result)) {
                $result[$key] = [];
            }
            $result[$key][] = $item;
            return $result;
        }, []);
        $list1 = $groupedData1['不遵守有关规定、不兑现有关工作承诺'] ?? [];
        $document -> setValue('dishonesty_1', implode("<w:br/>", array_column($list1, 'detail_context')));
        $list1 = $groupedData1['弄虚作假骗取荣誉、利益，虚报、谎报成绩'] ?? [];
        $document -> setValue('dishonesty_2', implode("<w:br/>", array_column($list1, 'detail_context')));
        $list1 = $groupedData1['个人档案造假'] ?? [];
        $document -> setValue('dishonesty_3', implode("<w:br/>", array_column($list1, 'detail_context')));
        $list1 = $groupedData1['不如实填报个人有关事项报告'] ?? [];
        $document -> setValue('dishonesty_4', implode("<w:br/>", array_column($list1, 'detail_context')));
        $list1 = $groupedData1['经济责任审计中存在违纪违规'] ?? [];
        $document -> setValue('dishonesty_5', implode("<w:br/>", array_column($list1, 'detail_context')));
        $list1 = $groupedData1['其他情况说明'] ?? [];
        $document -> setValue('dishonesty_6', implode("<w:br/>", array_column($list1, 'detail_context')));


        $annualEvaluationDatas = AnnualEvaluationModel::where($where)->select();
        $groupedData2 = array_reduce($annualEvaluationDatas->toArray(), function ($result, $item) {
            $key = $item['year'];
            if (!array_key_exists($key, $result)) {
                $result[$key] = [];
            }
            $result[$key][] = $item;
            return $result;
        }, []);

        $yearsArray = range(2015, 2023);
        foreach ($yearsArray as $year) {
            $datas = $groupedData2[strval($year)] ?? [];
            if(sizeof($datas)>0){
                $document -> setValue(strval($year), $datas[0]['evaluation_result']);
            }else{
                $document -> setValue(strval($year), '');
            }

        }

        $rewardPunishmentDatas = RewardPunishmentModel::where($where)->select();
        $groupedData3 = array_reduce($rewardPunishmentDatas->toArray(), function ($result, $item) {
            $key = $item['type'];
            if (!array_key_exists($key, $result)) {
                $result[$key] = [];
            }
            $result[$key][] = $item;
            return $result;
        }, []);
        $list1 = $groupedData3['奖励情况'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('rewards', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('rewards', '');
        }
        $list1 = $groupedData3['惩戒情况'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('punishments', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('punishments', '');
        }
        $list1 = $groupedData3['其他说明情况'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('reward_punish_addition', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('reward_punish_addition', '');
        }


        $socialCreditDatas = SocialCreditModel::where($where)->select();
        $groupedData4 = array_reduce($socialCreditDatas->toArray(), function ($result, $item) {
            $key = $item['type'];
            if (!array_key_exists($key, $result)) {
                $result[$key] = [];
            }
            $result[$key][] = $item;
            return $result;
        }, []);

        $list1 = $groupedData4['被采取强制措施(包括拘留)'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_1', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_1', '');
        }
        $list1 = $groupedData4['违反规定在企业或盈利性组织兼职'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_2', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_2', '');
        }
        $list1 = $groupedData4['未按规定申报审批、擅自办理因私出国(境)'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_3', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_3', '');
        }
        $list1 = $groupedData4['故意捏造、传播谣言，或在网络等媒体、平台发表不当言论'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_4', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_4', '');
        }
        $list1 = $groupedData4['利用职务之便，非法泄露国家秘密、个人隐私等信息，给国家、社会、他人造成重大损失或产生恶劣社会影响'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_5', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_5', '');
        }
        $list1 = $groupedData4['不履行法院生效判决或不配合法院依法强制执行'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_6', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_6', '');
        }
        $list1 = $groupedData4['存在酒驾、肇事逃逸交通违法行为'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_7', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_7', '');
        }
        $list1 = $groupedData4['存在银行个人信用不良记录'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_8', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_8', '');
        }
        $list1 = $groupedData4['其他说明情况'] ?? [];
        if(sizeof($list1)>0){
            $document -> setValue('social_credit_9', implode("<w:br/>", array_column($list1, 'detail_content')));
        }else{
            $document -> setValue('social_credit_9', '');
        }


        $sys_user = SysUserModel::get($sys_user_id);
        Hook::listen('logger', $sys_user['name'] . '导出' . $user['name'].'的诚信档案');

        $file_key= UUIDUtil::getUUID();
        $document->saveAs($this->save_path.$file_key.'.docx');
        $file_name = $this->save_path.$file_key.'.docx';
        $downFileName = urlencode("诚信档案");


        $response = new Response();
        $response->header('Content-type: application/octet-stream');
        $response->header('Content-disposition: attachment;filename*=utf-8\'\''. $downFileName . '.docx');
        $response->data(file_get_contents($file_name));
        return $response;
    }
}