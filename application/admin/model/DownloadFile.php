<?php

namespace app\admin\model;

use think\Db;
use think\Model;

/**
 * 下载文件
 */
class DownloadFile extends Model
{
    //初始化
    protected function initialize()
    {
        // 需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    /**
     * 获取单条下载文章的所有文件
     * @author 小虎哥 by 2018-4-3
     */
    public function getDownFile($aid, $field = '*')
    {
        $result = Db::name('DownloadFile')->field($field)
            ->where('aid', $aid)
            ->order('sort_order asc')
            ->select();

        foreach ($result as $key => $val) {
            if (!empty($val['file_url'])) {
                $result[$key]['file_url'] = handle_subdir_pic($val['file_url'], 'soft');
            }
        }

        return $result;
    }

    /**
     * 删除单条下载文章的所有文件
     * @author 小虎哥 by 2018-4-3
     */
    public function delDownFile($aid = array())
    {
        if (!is_array($aid)) {
            $aid = array($aid);
        }
        $result = Db::name('DownloadFile')->where(array('aid'=>array('IN', $aid)))->delete();
        if ($result !== false) {
            Db::name('download_log')->where(array('aid'=>array('IN', $aid)))->delete();
        }

        return $result;
    }



    /**
     * 保存下载文章的文件
     * @author 小虎哥 by 2018-4-3
     */
    public function savefile($aid, $post = array())
    {
        // 拼装本地链接数据
        $data = array();
        $fileupload = isset($post['fileupload']) ? $post['fileupload'] : array();
        if (!empty($fileupload)) {
            $sort_order = 0;
            foreach($fileupload['file_url'] as $key => $val)
            {
                if($val == null || empty($val))  continue;
                $title     = !empty($post['title']) ? $post['title'] : '';
                $file_size = isset($post['fileupload']['file_size'][$key]) ? $post['fileupload']['file_size'][$key] : 0;
                $file_mime = isset($post['fileupload']['file_mime'][$key]) ? $post['fileupload']['file_mime'][$key] : '';
                $uhash     = isset($post['fileupload']['uhash'][$key]) ? $post['fileupload']['uhash'][$key] : '';
                $md5file   = isset($post['fileupload']['md5file'][$key]) ? $post['fileupload']['md5file'][$key] : '';
                $file_name   = isset($post['fileupload']['file_name'][$key]) ? $post['fileupload']['file_name'][$key] : '';
                $file_ext   = isset($post['fileupload']['file_ext'][$key]) ? $post['fileupload']['file_ext'][$key] : '';
                ++$sort_order;
                $data[] = array(
                    'aid'        => $aid,
                    'title'      => $title,
                    'file_url'   => $val,
                    'extract_code'  => '',
                    'file_size'  => $file_size,
                    'file_ext'   => $file_ext,
                    'file_name'  => $file_name,
                    'file_mime'  => $file_mime,
                    'uhash'      => $uhash,
                    'md5file'    => $md5file,
                    'is_remote'  => 0,
                    'sort_order' => $sort_order,
                    'add_time'   => getTime(),
                );
            }
        }

        // 拼装远程链接数据
        $data_new   = array();
        if (!empty($post['remote_file'])) {
            $sort_order = $sort_order;
            foreach($post['remote_file'] as $kkk => $vvv)
            {
                if($vvv == null || empty($vvv)) continue;
                $title = !empty($post['server_name'][$kkk]) ? $post['server_name'][$kkk] : $post['title'];
                $extract_code = !empty($post['extract_code'][$kkk]) ? $post['extract_code'][$kkk] : '';
                ++$sort_order;
                $data_new[] = array(
                    'aid'        => $aid,
                    'title'      => $title,
                    'file_url'   => $vvv,
                    'extract_code' => $extract_code,
                    'file_size'  => '0',
                    'file_ext'   => '',
                    'file_name'  => $title,
                    'file_mime'  => '',
                    'uhash'      => md5($vvv),
                    'md5file'    => md5($vvv),
                    'is_remote'  => 1,
                    'sort_order' => $sort_order,
                    'add_time'   => getTime(),
                );
            }
        }
        
        $data_new_new = [];
        if (!empty($data) && !empty($data_new)) {
            // 数组合并
            $data_new_new = array_merge($data,$data_new);
        }else if (!empty($data)) {
            $data_new_new = $data;
        }else if (!empty($data_new)) {
            $data_new_new = $data_new;
        }

        // 添加到数据库
        if (!empty($data_new_new)) {
            // 删除
            $this->delDownFile($aid);
            // 批量添加
            M('DownloadFile')->insertAll($data_new_new);
        }
    }
}