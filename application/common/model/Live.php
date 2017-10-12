<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/9/10 16:43
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm
 * '-------------------------------------------------------------------*/

namespace app\common\model;

class Live extends BaseModel
{
    protected $pk = "id";
    protected $table = "resty_live"; //完整的表名

    /**
     * @param $data
     * @return array
     */
    public function store($data)
    {
        $result = $this->save($data);
        if (false === $result) {
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "添加成功"];
    }

    public function recordHandle($data)
    {
        if ($data['recordStatus'] == 1) {
            $url = "https://live.tinywan.com/control/record/stop?app=live&name={$data['id']}&rec=rec1";
            curl_request($url);
            $result = $res = $this->where('id', $data['id'])->update(['recordStatus' => 0]);
        } else {
            $url = "https://live.tinywan.com/control/record/start?app=live&name={$data['id']}&rec=rec1";
            curl_request($url);
            $result = $res = $this->where('id', $data['id'])->update(['recordStatus' => 1]);
        }
        if (false === $result) {
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "添加成功"];
    }
}
