<?php
/**
 * CSV Help
 *
 */

class Csv {
    /**
     * 把带有键名的数据转换成csv文件的内容。把键名作为csv的头内容
     * @param array $rows 需要成生的csv的数据
     */
    public static function array_to_csv($rows) {
        $render = '';
        $headers = array();
        $fp = tmpfile();
        // 写入header信息
        if (isset($rows[0])) {
            $headers = array_keys($rows[0]);
        }

        fputcsv($fp, $headers);

        foreach ($rows as $row) {
            $values = array_values($row);
            @fputcsv($fp, $values);
        }
        $length = ftell($fp);
        rewind($fp);
        $render = fread($fp, $length + 1);
        fclose($fp);

        return $render;
    }
    /**
     * 获取头信息
     * @param string $file 文件路径
     * @param int $offset_line 偏移多少行
     * @param int $read_line 读取多少行
     * @param int $read_max_length 读取一行的最大长度
     * @return array
     */
    public static function csv_to_array($file, $offset_line = 0, $read_line = 0, $read_max_length=0){
        $max_length = $read_max_length; 

        $handle = fopen($file, 'r');

        // 获取header的信息
        $keys = fgetcsv($handle, $max_length);
        foreach($keys as &$v){
            $v = mb_convert_encoding($v, 'utf-8','gbk');
        }

        $data = array();
        while ($row = fgetcsv($handle, $max_length)) {
            $new_row = array();
            //make array data
            if (implode('',$row) == '') {//void empty data
                continue;
            }
            // 如果设定了迁移的行数，将先移动到指定的行数后再读取数据
            if($offset_line>0){
                $offset_line--;
                continue;
            }
            foreach ($row as $k => $val) {
                $new_row[$keys[$k]] = mb_convert_encoding($val, 'utf-8', 'gbk');
            }

            $data[] = $new_row;
        }
        return array('keys'=>$keys, 'data'=>$data);
    }
}
