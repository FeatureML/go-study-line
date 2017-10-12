<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/27
 * Time: 22:01
 * 参考文章：https://github.com/PHPOffice/PHPExcel/wiki/User%20Documentation
 */

namespace app\frontend\controller;

use app\common\controller\BaseFrontend;
use file\CsvReader;
use redis\BaseRedis;
use think\Db;
use think\Log;

class Excel extends BaseFrontend
{
    /**
     * Redis Instance
     * @return \Redis
     * @static
     */
    public static function redis()
    {
        $redis = BaseRedis::Instance();
        $redis->connect("127.0.0.1", 6379);
        return $redis;
    }

    /**
     * [0] init
     * URL：http://test.thinkphp5-line.com/frontend/excel/multipleSheet
     */
    public function init()
    {
        $objPHPExcel = new \PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();

        $data = array(
            array('1', '小王', '男', '20', '100'),
            array('2', '小李', '男', '20', '101'),
            array('3', '小张', '女', '20', '102'),
            array('4', '小赵', '女', '20', '103')
        );
        // 3、直接加载数据块来填充数据（不推荐）
        $objSheet->fromArray($data);
        // 4、生成指定格式(2007excel格式)xlsx文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition:attachment;filename=demo.xlsx');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * [1] Excel 入门简单的测试
     */
    public function index()
    {
        //创建对象
        $excel = new \PHPExcel();
        //Excel表格式,这里简略写了8列
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'F', 'G');
        //表头数组
        $tableheader = array('ID', '标题', '内容');
        //填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        $data = array(
            array('1', '小王', '男', '20', '100'),
            array('2', '小李', '男', '20', '101'),
            array('3', '小张', '女', '20', '102'),
            array('4', '小赵', '女', '20', '103')
        );

        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel5($excel);
        //$write->save(ROOT_PATH . 'public' . DS . 'Excel'.date('Y-m-d',time()).mt_rand(0,999).'.xls'); resty_invitation_live_info
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="testdata.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    /**
     * [3] 多个sheet的操作
     *     默认会自动创建一个sheet,以下案例创建3个sheet
     *     URL：http://test.thinkphp5-line.com/frontend/excel/multipleSheet
     */
    public function multipleSheet()
    {
        $liveId = "L02359";
        //0、实例化PHPExcel类，等同于在桌面新建一个Excel文件
        $objPHPExcel = new \PHPExcel();
        //1、循环创建多个sheet
        for ($i = 1; $i <= 3; $i++) {
            // [创建] 如果是多个sheet,则创建多个sheet
            if ($i > 1) $objPHPExcel->createSheet();//创建新的内置标
            // [设置] 把新创建的sheet设置为当前的活动sheet
            $objPHPExcel->setActiveSheetIndex($i - 1);
            // [获取] 获取当前的活动sheet
            $objSheet = $objPHPExcel->getActiveSheet();
            // [设置] 设置sheet名称
            $objSheet->setTitle("部门" . $i);
            // [填充] 填充表格第一列(表格头部)
            $objSheet->setCellValue("A1", "活动ID")
                ->setCellValue("B1", "用户昵称")
                ->setCellValue("C1", "邀请码")
                ->setCellValue("D1", "创建时间")
                ->setCellValue("E1", "使用时间");
            // [填充] 循环填充数据
            $data = Db::table("resty_invitation_info")
                ->alias('l')
                ->join("resty_invitation i", "i.infoId = l.id")
                ->where("l.liveId", $liveId)
                ->select();
            //从第二行开始填充
            $j = 2;
            foreach ($data as $key => $val) {
                $objSheet->setCellValue("A" . $j, $val["liveId"])
                    ->setCellValue("B" . $j, $val["userId"])
                    ->setCellValue("C" . $j, $val["code"])
                    ->setCellValue("D" . $j, $val["createTime"])
                    ->setCellValue("E" . $j, $val["usedTime"]);
                $j++;
            }
        }
        // 4、生成指定格式(2007excel格式)xlsx文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition:attachment;filename=' . $liveId . '活动邀请码.xlsx');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     *  [4] Excel样式控制
     *  功能：单元格合并、设置行高、设置默认字体、设置字体颜色
     *  URL：http://test.thinkphp5-line.com/frontend/excel/styleControl
     */
    public function styleControl()
    {
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
        //设置默认行高
        $objSheet->getDefaultRowDimension()->setRowHeight(20);
        //设置列的宽度
        $objSheet->getDefaultColumnDimension()->setWidth(20);
        $objSheet->getColumnDimension("A")->setWidth(10);
        $objSheet->getColumnDimension("B")->setWidth(10);
        $objSheet->getColumnDimension("C")->setWidth(10);
        // 设置默认字体
        $objSheet->getDefaultStyle()->getFont()->setSize(14)->setName("华文宋体");

        $objSheet->getStyle("A3:G3")->getFont()->setBold(true);
        $objSheet->getStyle("A2:K2")->getFont()->setSize(20)->setBold(true);
        $objSheet->getRowDimension("A2:K2")->setRowHeight(40);

        //填充表头信息
        $objSheet->setCellValue("A4", "活动ID");
        $objSheet->setCellValue("B4", "用户昵称");
        $objSheet->setCellValue("C4", "邀请码");
        $objSheet->setCellValue("D4", "创建时间");
        $objSheet->setCellValue("E4", "使用时间");
        $objSheet->setCellValue("F4", "到期时间");
        $objSheet->setCellValue("G4", "联系电话");

        $objSheet->setCellValue("A2", $liveId . "微信邀请码的详细信息一览表");
        $objSheet->setCellValue("A3", "活动基本信息");
        $objSheet->setCellValue("D3", "时间基本信息");
        //合并单元格
        $objSheet->mergeCells('A2:K2');
        $objSheet->mergeCells('A3:C3');
        $objSheet->mergeCells('D3:G3');
        // 单元格添加边框
        //给表格添加数据
        for ($i = 0; $i < count($tableHeader); $i++) {
            //查询数据库
            $data = Db::table("resty_invitation_info")
                ->alias('l')
                ->join("resty_invitation i", "i.infoId = l.id")
                ->where("l.liveId", $liveId)
                ->select();
            $j = 5;
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
                }
                $j++;
            }
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition:attachment;filename=' . $liveId . '活动邀请码.xlsx');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * [2] 功能：导出Mysql数据数据到Excel表格，根据活动好导出导出邀请码数据
     *     需求：可以根据Get过来的参数拼装成一个数组，然后根据数组的长度来打印所需要的格式
     *     URL：http://test.thinkphp5-line.com/frontend/excel/findMysqlToExcelTable
     */
    public function findMysqlToExcelTable()
    {
        $liveId = "L02359";
        //创建对象
        $objPHPExcel = new \PHPExcel();
        //设置文件的一些属性，在xls文件——>属性——>详细信息里可以看到这些值，xml表格里是没有这些值的
        $objPHPExcel
            ->getProperties()//获得文件属性对象，给下文提供设置资源
            ->setCreator("Tinywan")
            ->setTitle($liveId . "活动邀请码 Excel 表格")
            ->setSubject("Office 2007 XLSX Test Document");

        //这里是根据Get过来的数组判断要导出的Excel的列数目
        $letterPost = ['11A', 'B11', 'C111', 'D1', 'D1', 'D1', 'D123423432'];
        static $letter = [];
        //获取Excel 头部的大写字母
        for ($i = 65; $i < (64 + count($letterPost)); $i++) {
            $letter[] = strtoupper(chr($i));
        }
        //表头数组
        $tableHeader = ['活动ID', '用户昵称', '邀请码', '邀请码创建时间', '邀请码使用时间', '到期时间'];
        $objSheet = $objPHPExcel->getActiveSheet();   //获取当前活动sheet
        $objSheet->setTitle($liveId . " 活动邀请码");   //给当前活动sheet起个名称
        //给表格添加数据
        for ($i = 0; $i < count($tableHeader); $i++) {
            //查询数据库
            $data = Db::table("resty_invitation_info")
                ->alias('l')
                ->join("resty_invitation i", "i.infoId = l.id")
                ->where("l.liveId", $liveId)
                ->select();
            //填充表头信息
            $objPHPExcel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableHeader[$i]");
            $j = 2;
            //填充表格信息
            foreach ($data as $key => $val) {
                $objSheet->setCellValue("A" . $j, $val["liveId"])
                    ->setCellValue("B" . $j, $val["userId"])
                    ->setCellValue("C" . $j, $val["code"])
                    ->setCellValue("D" . $j, $val["createTime"])
                    ->setCellValue("E" . $j, $val["usedTime"])
                    ->setCellValue("F" . $j, $val["createTime"]);
                $j++;
            }
        }

        $write = new \PHPExcel_Writer_Excel5($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=' . $liveId . '活动邀请码.xls');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    /**
     * [3] 功能：导出Mysql数据数据到Excel表格
     *     需求：设置输出Excel格式的样式，文本格式，
     *     URL：http://www.tinywan_thinkphp5.com/frontend/excel/readMysqlToExcelTable
     */
    public function readMysqlToExcelTable()
    {
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
                ->where("l.liveId", $liveId)
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

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition:attachment;filename=' . $liveId . '活动邀请码.xlsx');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * 读取Excel 表格
     */
    public function readExcel()
    {
        $basePath = ROOT_PATH . 'public' . DS;
        $inputFileName = $basePath . 'uploads/L02359.xlsx';
        // Check if file exists
        if (empty($inputFileName) or !file_exists($inputFileName)) {
            return "file not exists";;
        }
        $fileType = \PHPExcel_IOFactory::identify($inputFileName);
        $objReader = new \PHPExcel_Reader_Excel2007($fileType);  //获取文件读取操作对象Excel2007
        if ($fileType == 'Excel5') {
            $objReader = new \PHPExcel_Reader_Excel5($fileType);    //Excel2003
        }
        $sheetName = array("L02359 活动邀请码", "市场部", "技术部", "研发部");    //需要读取的sheet名称
        $objReader->setLoadSheetsOnly("L02359 活动邀请码");    //只加载指定的sheet

        try {
            $objPHPExcel = $objReader->load($inputFileName);     //加载文件
        } catch (\PHPExcel_Reader_Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        // 一次性加载所有数据
        $sheetCount = $objPHPExcel->getSheetCount();    //获取excel文件里有多少个sheet
        for ($i = 0; $i < $sheetCount; $i++) {
            $data = $objPHPExcel->getSheet($i)->toArray();  //读取每个sheet里的数据 全部放入到数组中
//            print_r($data);
        }

        $dataArr = array();
        foreach ($objPHPExcel->getWorksheetIterator() as $sheet) //循环取sheet
        {
            foreach ($sheet->getRowIterator() as $row) {  //逐行处理
                foreach ($row->getCellIterator() as $cell) {  //逐列读取
                    $dataArr[] = $cell->getValue();    //获取单元格数据
                }
            }
        }
        halt($dataArr);
    }

    /**
     * 读取Excel 表格数据插入到Mysql数据库中去
     */
    public function readExcelInsertMysql($filePath, $sheet = 0)
    {
        // Check if file exists
        if (empty($filePath) or !file_exists($filePath)) {
            return "file not exists";;
        }
        $PHPReader = new \PHPExcel_Reader_Excel2007();  //获取文件读取操作对象Excel2007
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if ($PHPReader->canRead($filePath)) {
                return "no Excel";
            }
        }

        try {
            //建立excel对象 加载文件
            $PHPExcel = $PHPReader->load($filePath);
            //**读取excel文件中的指定工作表*/
            $currentSheet = $PHPExcel->getSheet($sheet);
            //**取得最大的列号*/
            $allColumn = $currentSheet->getHighestColumn();
            //**取得一共有多少行*/
            $allRow = $currentSheet->getHighestRow();
            $dataArr = [];

            //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++) {
                for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                    $addr = $colIndex . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    //富文本转换字符串
                    if ($cell instanceof \PHPExcel_RichText) {
                        $cell = $cell->__toString();
                    }
                    $dataArr[$rowIndex][$colIndex] = $cell;
                }
            }
        } catch (\PHPExcel_Reader_Exception $e) {
            die('Error loading file "' . pathinfo($filePath, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }
        return $dataArr;
    }

    /**
     *  数据验证码发送
     *  需求：自动给客户发送微信邀请码
     *  功能：
     *      1、客户自动导入Excel表格（命名规则为liveId）
     *      2、根据LiveId 查询对应的数据的微信邀请码
     *      3、循环遍历发送给指定的手机号码
     *      4、插入队列发送，待做...
     *  URL：http://test.thinkphp5-line.com/frontend/excel/readExcelInsertMysqlTest
     */
    public function readExcelInsertMysqlTest()
    {
        $basePath = ROOT_PATH . 'public' . DS;
        $filePath = $basePath . 'uploads/L02359.xlsx';
        //获取文件名称
        $liveId = pathinfo($filePath, PATHINFO_FILENAME);
        $res = $this->readExcelInsertMysql($filePath);
        // 存储所有Tel 到一个数组中去
        $telArr = [];
        foreach ($res as $key => $value) {
            $telArr[] = $value["G"];
        }
        //删除读取Excel表格的头部,剩下的全部为一个手机号码的数组集合
        unset($telArr[0]);
        /**
         *  循环$telArr 手机号码数组，发送短信,查询数据库 status == NULL 的验证码
         */
        $codeArr = Db::table("resty_invitation_info")
            ->alias('l')
            ->join("resty_invitation i", "i.infoId = l.id")
            ->where("l.liveId", $liveId)
            ->whereNull('i.status')
            ->select();
        /**
         * 根据手机号码发送邀请码
         */
        try {
            for ($j = 1; $j < count($codeArr) + 1; $j++) {
                if ($j > count($telArr)) break;
                echo $telArr[$j] . '--' . $codeArr[$j]["code"] . "<br/>";
                // $resTT = send_dayu_sms($telArr[$j], "live", ["number" => '12', 'code' => $codeArr[$j]["code"]]);
                $resTT = send_dayu_sms($telArr[$j], "register", ["number" => '12', 'code' => $codeArr[$j]["code"]]);
                //判断返回的是否是一个对象，发送验证成功，修改当前数据的状态数据
                if (isset($resTT->result->success) && ($resTT->result->success == true)) {
                    Db::table("resty_invitation")->where('code', $codeArr[$j]["code"])->update([
                        "send_time" => date("Y-m-d H:i:s", time()),
                        "send_tel" => $telArr[$j],
                        "status" => 1
                    ]);
                    Log::info("--------------------验证码 : " . $codeArr[$j]["code"] . " 发送成功");
                } else {
                    Log::error("-------------------验证码 : " . $codeArr[$j]["code"] . " 发送失败 ，错误原因：" . $resTT->sub_msg);
                }
            }
        } catch (\Exception $e) {
            Log::error("-----------验证码发送抛出异常 : " . $e->getMessage());
        }

    }

    /**
     * Excel 队列的加入
     */
    public function addExcelList()
    {
        // 接受用户ID
        $liveId = request()->get("liveId");
        // 请输入要接受的邮箱信息
        $email = request()->get("email");
        Log::info("[ . self::formatDate(time()) . ']: 插入了一个新的任务:" . $email);
        if (empty($liveId) || empty($email)) return json(["code" => 403, "msg" => '参数不能为空']);
        // cli 去执行这个命令吧
        $exportExcelUrl = $_SERVER["HTTP_HOST"] . "/frontend/excel/exportExcelList?liveId={$liveId}";
        $exportExcelCallBackUrl = $_SERVER["HTTP_HOST"] . "/frontend/excel/exportExcelListCallBack?liveId={$liveId}&email={$email}";
        echo '------------------------------------启动一个CLi进程 开始--------------------------------';
        exec("/usr/bin/php /home/www/tinywan/cli/send_mail.php '{$exportExcelUrl}' '{$exportExcelCallBackUrl}' >/dev/null 2>&1 &", $output, $return_val);
        if ($return_val != 0) Log::error('[' . $this->formatDate(time()) . ']:' . '插入了一个新的任务事件脚本执行错误');
        Log::info('[' . $this->formatDate(time()) . ']:' . '事件脚本执行OK');
        echo '-----------------------------------启动一个CLi进程 结束----------------------------------';
        exit("执行成功");
    }

    /**
     * Excel 导出队列
     */
    public function exportExcelList()
    {
        Log::info('[' . self::formatDate(time()) . ']: Excel 导出队列任务处理开始');
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
                ->where("l.liveId", $liveId)
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

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $basePath = ROOT_PATH . 'public' . DS;
        $inputFileName = $basePath . "uploads/{$liveId}.xlsx";
        $objWriter->save($inputFileName);
        return json(["code" => 200, "msg" => 'finish']);
    }

    /**
     * Excel 表格导出成功后执行的回调函数
     */
    public function exportExcelListCallBack()
    {
        // 接受用户ID
        $liveId = request()->get("liveId");
        // 请输入要接受的邮箱信息
        $email = request()->get("email");
        $str = <<<html
            您好！ <h1>$liveId</h1> 活动的评论导出详细信息：<p></p>
html;
        $basePath = ROOT_PATH . 'public' . DS;
        $fileName = $liveId . '.xlsx';
        $filePath = $basePath . 'uploads/' . $fileName;
        //传递一个数组，可以实现多邮件发送,有人注册的时候给管理员也同时发送一份邮件
        $result = send_email($email, '邀请码的详细信息已经发动到你的邮箱', $str, $filePath, $fileName);
        halt($result);
    }

    /**
     * ------------------------------------------------------------------------邮件队列信息---------------------------
     * 把邮件写入Redis队列
     */
    public function addRedisListToMail()
    {
        $redis = self::redis();
        //接受用户ID
        $taskId = request()->get("mail");
        echo $taskId . "<br/>";
        $TaskName = "task-queue";
        /**
         * 【1】将任务插入任务队列：task-queue
         * 【2】Redis Lpush 命令将一个或多个值插入到列表头部。 如果 key 不存在，一个空列表会被创建并执行 LPUSH 操作。 当 key 存在但不是列表类型时，返回一个错误。
         * 【3】如果直接存贮的话有一个问题，邮件发送失败
         */
        $addRes = $redis->lPush($TaskName, json_encode($taskId));
        if (false === $addRes) exit("添加失败");
        Log::info("[ . self::formatDate(time()) . ']: 插入了一个新的任务:" . $taskId);
        // cli 去执行这个命令吧
        $sendURL = $_SERVER["HTTP_HOST"] . "/frontend/excel/redisListSendMail";
        echo '------------------------------------启动一个CLi进程 开始--------------------------------';
        exec("/usr/bin/php /home/www/tinywan/cli/send_mail.php '{$sendURL}' >/dev/null 2>&1 &", $output, $return_val);
        if ($return_val != 0) Log::error('[' . $this->formatDate(time()) . ']:' . '插入了一个新的任务事件脚本执行错误');
        Log::info('[' . $this->formatDate(time()) . ']:' . '事件脚本执行OK');
        echo '-----------------------------------启动一个CLi进程 结束----------------------------------';
        exit("执行成功");
    }

    /**
     * 使用Redis 队列发送邮件
     */
    public function redisListSendMail()
    {
        Log::info('[' . self::formatDate(time()) . ']: 开始任务处理，被清除');
        while (true) {
            $redis = self::redis();
            // 列表长度为空的，跳出该循环体
            if ($redis->lLen("task-queue") == 0) break;
            // $taskId == mail
            $taskId = $redis->rpoplpush("task-queue", "tmp-queue");
            $email = json_decode($taskId);
            //发送邮件了
            sleep(5);
            $email_code = mt_rand(1111, 9999);
            $sendResult = send_email($email, 'Redis List 物联网智能数据邮件验证码:', $email_code);
            if ($sendResult["error"] == 0) {
                // 将本次任务从暂存队列"tmp-queue"中清除,Redis rPop 命令用于移除并返回列表的最后一个元素
                $redis->rPop("tmp-queue");
                Log::info('[' . self::formatDate(time()) . ']:' . $taskId . " 任务处理成功，被清除");
            } else {
                //将本次处理失败的任务从暂存队列"tmp-queue"中，弹回任务队列"task-queue"
                $redis->rpoplpush("tmp-queue", "task-queue");
                Log::error('[' . self::formatDate(time()) . ']:' . $taskId . " 任务处理失败，任务已经被弹回,错误原因: " . $sendResult["message"]);
            }
            sleep(10);
        }
        return json(["code" => 200, "msg" => 'finish']);
    }

    /**
     * 使用Redis 队列发送邮件
     */
    public function sendMail()
    {
        $liveId = "L02359";
        $str = <<<html
            您好！ <h1>$liveId</h1> 活动的评论导出详细信息：<p></p>
html;
        $basePath = ROOT_PATH . 'public' . DS;
        $fileName = 'L02359.xlsx';
        $filePath = $basePath . 'uploads/' . $fileName;
        //传递一个数组，可以实现多邮件发送,有人注册的时候给管理员也同时发送一份邮件
        $result = send_email_file("756684177@qq.com", '可以实现多邮件发送,有人注册的时候给管理员也同时发送一份邮件 物联网智能数据 邮件验证码：', $str, $filePath, $fileName);
        halt($result);
    }

    /**
     * 单条短信测试Excel.php
     */
    public function sendDayuSmsPlus()
    {
        $res = send_dayu_sms("13669361192", "register", ['code' => rand(100000, 999999)]);
        halt($res);
    }

    /**
     * -------------------------------------------------CSV 部分--------------------------------------------------------
     * 【1】csv 数据格式导出
     * @param $filename
     * @param $data
     */
    /**
     * Csv 导出数据
     * @param $filename
     * @param $data
     */
    protected function export_csv($filename, $data)
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
    }

    /**
     * 【2】Csv 数据表格填充
     */
    public function exportCsv()
    {
        $liveId = "L01410";
        $result = Db::table("resty_invitation_info")
            ->alias('l')
            ->join("resty_invitation i", "i.infoId = l.id")
            ->where("l.liveId", $liveId)
            ->select();
        $str = "活动ID,用户昵称,邀请码,创建时间,使用时间,到期时间,联系电话\n";
        //中文转码
        $str = iconv('utf-8', 'gb2312', $str);
        foreach ($result as $key => $val) {
            $liveId = iconv('utf-8', 'gb2312', $val['liveId']);
            $userId = iconv('utf-8', 'gb2312', $val['userId']);
            $str .= $liveId . "," . $userId . "," . $val['code'] . "," . $val['createTime'] . "," . $val['usedTime'] . "," . $val['send_time'] . "," . $val['tel'] . "\n"; //用引文逗号分开
        }
        $filename = $liveId . '.csv'; //设置文件名
        $this->export_csv($filename, $str); //导出
    }

    /**
     * 【3】读取CVS数据到Mysql数据库
     * @param $filePath
     * @return bool|string
     */
    public function cvsInsertToMysql($filePath)
    {
        $reader = new CsvReader($filePath);
        $data = $reader->get_data(1000, 0);
        //取出表头信息
        unset($data[0]);
        // 拼接插入的数组
        $tmpArr = [];
        for ($i = 1; $i < count($data); $i++) {
            for ($j = 0; $j <= 6; $j++) {
                $tmpArr[$i - 1] = [
                    'name' => $data[$i][0],
                    'mobile' => $data[$i][6],
                    'agent' => $data[$i][2]
                ];
            }
        }
        // 插入数据库
        try {
            foreach ($tmpArr as $insert) {
                $hasMobile = Db::table('resty_address')->where('mobile', $insert['mobile'])->find();
                if ($hasMobile == true) continue;
                $insertRes = Db::table('resty_address')->insert($insert);
                Log::info('[' . getCurrentDate() . ']: 号码=' . $insert['mobile'] . " CVS数据插入成功：" . $insertRes);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('[' . getCurrentDate() . ']:' . $insert['mobile'] . " CVS数据插入失败：" . json_encode($e->getMessage()));
            return $e->getMessage();
        }
    }

    /**
     * 【3】读取CVS数据到Mysql数据库
     */
    public function readCvsToMysql()
    {
        $basePath = ROOT_PATH . 'public' . DS;
        $filePath = $basePath . 'uploads/L02359.csv';
        $res = $this->cvsInsertToMysql($filePath);
        halt($res);
    }

    public function readTestMysql()
    {
        var_dump(getCurrentDate());
    }
}