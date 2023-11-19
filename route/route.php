<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;

Route::get('v1/test', 'api/v1.Test/index');

Route::group('', function () {
    Route::group('cms', function () {
        // 账户相关接口分组
        Route::group('user', function () {
            // 登陆接口
            Route::post('login', 'api/cms.User/userLogin');
            // 刷新令牌
            Route::get('refresh', 'api/cms.User/refreshToken');
            // 查询自己拥有的权限
            Route::get('permissions', 'api/cms.User/getAllowedApis');
            // 注册一个用户
            Route::post('register', 'api/cms.User/register');
            // 查询自己信息
            Route::get('information', 'api/cms.User/getInformation');
            // 用户更新信息
            Route::put('', 'api/cms.User/update');
            // 修改自己密码
            Route::put('change_password', 'api/cms.User/changePassword');
        });
        // 管理类接口
        Route::group('admin', function () {
            // 查询所有可分配的权限
            Route::get('permission', 'api/cms.Admin/getAllPermissions');
            // 查询所有用户
            Route::get('users', 'api/cms.Admin/getAdminUsers');
            // 修改用户密码
            Route::put('user/:id/password', 'api/cms.Admin/changeUserPassword');
            // 删除用户
            Route::delete('user/:id', 'api/cms.Admin/deleteUser');
            // 更新用户信息
            Route::put('user/:id', 'api/cms.Admin/updateUser');
            // 查询所有权限组
            Route::get('group/all', 'api/cms.Admin/getGroupAll');
            // 新增权限组
            Route::post('group', 'api/cms.Admin/createGroup');
            // 查询指定分组及其权限
            Route::get('group/:id', 'api/cms.Admin/getGroup');
            // 更新一个权限组
            Route::put('group/:id', 'api/cms.Admin/updateGroup');
            // 删除一个分组
            Route::delete('group/:id', 'api/cms.Admin/deleteGroup');
            // 删除多个权限
            Route::post('permission/remove', 'api/cms.Admin/removePermissions');
            // 分配多个权限
            Route::post('permission/dispatch/batch', 'api/cms.Admin/dispatchPermissions');

        });
        // 日志类接口
        Route::group('log', function () {
            Route::get('', 'api/cms.Log/getLogs');
            Route::get('users', 'api/cms.Log/getUsers');
            Route::get('search', 'api/cms.Log/getUserLogs');
        });
        //上传文件类接口
        Route::post('file', 'api/cms.File/postFile');
    });
    Route::group('v1', function () {
        Route::group('book', function () {
            // 查询所有图书
            Route::get('', 'api/v1.Book/getBooks');
            // 新建图书
            Route::post('', 'api/v1.Book/create');
            // 查询指定bid的图书
            Route::get(':bid', 'api/v1.Book/getBook');
            // 搜索图书

            // 更新图书
            Route::put(':bid', 'api/v1.Book/update');
            // 删除图书
            Route::delete(':bid', 'api/v1.Book/delete');
        });

    });
    Route::group('responsibility_fulfillment', function () {
        Route::post('', 'api/cms.ResponsibilityFulfillment/create');
        Route::put('', 'api/cms.ResponsibilityFulfillment/update');
        Route::delete('', 'api/cms.ResponsibilityFulfillment/delete');
        Route::post('list', 'api/cms.ResponsibilityFulfillment/list');
    });
    Route::group('sys_user', function () {
        Route::post('login', 'api/user.SysUser/login');
        Route::post('logout', 'api/user.SysUser/logout');
        Route::get('getUserInfo', 'api/user.SysUser/getUserInfo');
    });
    Route::group('user', function () {
        Route::get('', 'api/user.User/getUser');
        Route::post('search', 'api/user.User/listUsers');
        Route::post('', 'api/user.User/addUser');
        Route::put('', 'api/user.User/updateUser');
        Route::delete('', 'api/user.User/deleteUser');
        Route::get('by_dept', 'api/user.User/getUserByDeptId');
    });
    Route::group('permission', function () {
        Route::get('getRouters', 'api/permission.Menu/getRouters');
    });
    Route::group('system', function () {
        Route::group('user', function () {
            Route::post('list', 'api/user.SysUser/listUsers');
            Route::post('', 'api/user.SysUser/addUser');
            Route::put('', 'api/user.SysUser/updateUser');
            Route::delete('', 'api/user.SysUser/deleteUser');
            Route::put('changeStatus', 'api/user.SysUser/changeStatus');
        });

        Route::group('role', function () {
            Route::get('', 'api/permission.Role/getRole');
            Route::get('list', 'api/permission.Role/listAllRoles');
            Route::post('search', 'api/permission.Role/listRoles');
            Route::post('', 'api/permission.Role/addRole');
            Route::put('', 'api/permission.Role/updateRole');
            Route::delete('', 'api/permission.Role/deleteRole');
        });

        Route::group('dept', function () {
            Route::post('tree', 'api/permission.Dept/listDeptTree');
            Route::get('list', 'api/permission.Dept/getDeptList');
            Route::post('list/exclude', 'api/permission.Dept/listDeptExclude');
            Route::get('', 'api/permission.Dept/getDept');
            Route::post('', 'api/permission.Dept/addDept');
            Route::put('', 'api/permission.Dept/updateDept');
            Route::delete('', 'api/permission.Dept/deleteDept');
        });

        Route::group('menu', function () {
            Route::get('list', 'api/permission.Menu/getMenus');
            Route::get('', 'api/permission.Menu/getMenu');
            Route::post('', 'api/permission.Menu/addMenu');
            Route::put('', 'api/permission.Menu/updateMenu');
            Route::delete('', 'api/permission.Menu/deleteMenu');
            Route::get('treeSelect', 'api/permission.Menu/treeSelect');
            Route::get('roleMenuTreeSelect', 'api/permission.Menu/roleMenuTreeSelect');
        });
    });
    Route::group('document', function () {
        Route::group('dishonesty', function () {
            Route::post('search', 'api/honestDocument.Dishonesty/searchData');
            Route::post('', 'api/honestDocument.Dishonesty/addData');
            Route::put('', 'api/honestDocument.Dishonesty/updateData');
            Route::delete('', 'api/honestDocument.Dishonesty/deleteData');
        });
        Route::group('annual_evaluation', function () {
            Route::post('search', 'api/honestDocument.AnnualEvaluation/searchData');
            Route::post('', 'api/honestDocument.AnnualEvaluation/addData');
            Route::put('', 'api/honestDocument.AnnualEvaluation/updateData');
            Route::delete('', 'api/honestDocument.AnnualEvaluation/deleteData');
        });
        Route::group('reward_punishment', function () {
            Route::post('search', 'api/honestDocument.RewardPunishment/searchData');
            Route::post('', 'api/honestDocument.RewardPunishment/addData');
            Route::put('', 'api/honestDocument.RewardPunishment/updateData');
            Route::delete('', 'api/honestDocument.RewardPunishment/deleteData');
        });
        Route::group('social_credit', function () {
            Route::post('search', 'api/honestDocument.SocialCredit/searchData');
            Route::post('', 'api/honestDocument.SocialCredit/addData');
            Route::put('', 'api/honestDocument.SocialCredit/updateData');
            Route::delete('', 'api/honestDocument.SocialCredit/deleteData');
        });
    });
    Route::group('inspection', function () {
        Route::post('search', 'api/record.InspectionRecord/searchData');
        Route::post('', 'api/record.InspectionRecord/addData');
        Route::put('', 'api/record.InspectionRecord/updateData');
        Route::delete('', 'api/record.InspectionRecord/deleteData');
    });
    Route::group('loan', function () {
        Route::post('search', 'api/record.LoanRecord/searchData');
        Route::post('', 'api/record.LoanRecord/addData');
        Route::put('', 'api/record.LoanRecord/updateData');
        Route::delete('', 'api/record.LoanRecord/deleteData');
    });
    Route::group('file', function () {
        Route::get('export', 'api/file.FileExport/exportUserInfo');
        Route::post('import', 'api/file.FileImport/importUserInfo');

    });
    Route::group('statistic', function () {
        Route::get('document_upload_statistic', 'api/statistic.Statistic/getDocumentUploadStatistics');
        Route::get('daily_document_upload_statistic', 'api/statistic.Statistic/getDailyDocumentUploadStatistics');
        Route::post('login_records', 'api/statistic.Statistic/listLoginRecord');
    });
})->middleware(['Authentication', 'ReflexValidate'])->allowCrossDomain(true, $header = [
    'Access-Control-Allow-Credentials' => 'true',
    'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE',
    'Access-Control-Allow-Headers' => 'tag, Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With',
]);

