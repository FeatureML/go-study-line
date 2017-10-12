<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/9/30 21:26
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm
 * '-------------------------------------------------------------------*/

namespace app\backend\controller;


use app\common\controller\BaseBackend;
use think\Db;

class Office extends BaseBackend
{
    /**
     * 列表显示
     * @return mixed
     */
    public function index()
    {
        $liveId = 'L02359';
        $lists = Db::table("resty_invitation_info")
            ->alias('l')
            ->join("resty_invitation i", "i.infoId = l.id")
            ->where("l.liveId", $liveId)
            ->paginate(14);
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /**
     * Csv 导出数据
     * @param $filename
     * @param $data
     */
    protected function export_excel($objPHPExcel, $liveId)
    {
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition:attachment;filename=' . $liveId . '活动邀请码.xlsx');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * MySQL数据到Excel表格
     */
    public function mysqlToExcel()
    {
        if (request()->isPost()) {
            $actionArr = input('post.subcheck/a');
            $liveId = "L02359";
            //创建对象
            $objPHPExcel = new \PHPExcel();
            //这里是根据Get过来的数组判断要导出的Excel的列数目
            $letterPost = ['11A', 'B11', 'C111', 'D1', 'D1', 'D1', 'D123423432', "weqweqw"];
            static $letter = [];
            //获取Excel 头部的大写字母
            for ($i = 65; $i < (64 + count($letterPost)); $i++) {
                $letter[] = strtoupper(chr($i));
            }

            //表头数组
            $tableHeader = ['活动ID', '用户昵称', '邀请码', '邀请码创建时间', '邀请码使用时间', '到期时间', "联系电话"];
            $objSheet = $objPHPExcel->getActiveSheet();   //获取当前活动sheet
            $objSheet->setTitle($liveId . " 活动邀请码");   //给当前活动sheet起个名称
            // 设置水平垂直居中
            $objSheet->getDefaultRowDimension()->setVisible(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            // 设置默认字体
            $objSheet->getDefaultStyle()->getFont()->setSize(14)->setName("华文宋体");
            $objSheet->getStyle("A1:F1")->getFont()->setBold(true);
            //设置默认行高
            $objSheet->getDefaultRowDimension()->setRowHeight(20);
            //设置列的宽度
            $objSheet->getDefaultColumnDimension()->setWidth(20);
            $objSheet->getColumnDimension("A")->setWidth(10);
            $objSheet->getColumnDimension("B")->setWidth(10);
            $objSheet->getColumnDimension("C")->setWidth(10);

            //给表格添加数据
            for ($i = 0; $i < count($tableHeader); $i++) {
                //查询数据库
                $data = Db::table("resty_invitation_info")
                    ->alias('l')
                    ->join("resty_invitation i", "i.infoId = l.id")
                    ->whereIn('i.id', $actionArr)
                    ->select();
                //填充表头信息
                $objSheet->setCellValue("$letter[$i]1", "$tableHeader[$i]");
                $j = 2;
                //填充表格信息
                foreach ($data as $key => $val) {
                    $objSheet->setCellValue("A" . $j, $val["liveId"])
                        ->setCellValue("B" . $j, $val["userId"])
                        ->setCellValue("C" . $j, $val["code"])
                        ->setCellValue("D" . $j, $val["createTime"])
                        ->setCellValue("E" . $j, $val["usedTime"])
                        ->setCellValue("F" . $j, $val["createTime"])
                        ->setCellValue("G" . $j, $val["tel"]);
                    // 设置格式
                    if ($val["userId"] != 0) {
                        // 设置字体颜色
                        $objSheet->getStyle("A" . $j . ":F" . $j)->getFont()->setUnderline(\PHPExcel_Style_Font::UNDERLINE_SINGLE)->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_RED);
                        //设置背景颜色
                        $objSheet->getStyle("E" . $j)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB(\PHPExcel_Style_Color::COLOR_GREEN);
                    }
                    $j++;
                }
            }
            $this->export_excel($objPHPExcel, $liveId);
        }
    }
}