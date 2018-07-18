<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * $Id: BitMapUtil.php 2017/11/3  20:18 chenjingxiu $
 */
class BitMapUtil
{
    /**
     * hash后缀(%u,%d,%l当处理1 << 30时在win64会出现整形溢出,sprintf的bug)
     * @var string
     */
    static $format = '%.0f.%d';
    /**
     * 设置当前位置的bitmap
     * @param $hash  当前散列位置
     * @param $base_hash int 初始hash
     * @return int
     */
    public static function do_hash($hash, $base_hash = 0)
    {
        if($hash < 1){
            return NULL;
        }
        if($base_hash < 1){
            return  1 << ($hash - 1);
        }
        return (1 << ($hash-1)) + $base_hash;
    }

    /**
     * 带后缀的hash
     * @param $hash
     * @param int $bash_hash
     * @param int $append
     * @return float
     */
    public static function with_hash($hash, $bash_hash = 0, $append = 0)
    {
        $hash = static::do_hash($hash, $bash_hash);
        return static::with($hash, $append);
    }

    /**
     * 获取hash当前设定值
     * @param $base_hash
     * @param $hash
     * @return  int
     */
    public static function check_hash($base_hash, $hash)
    {
        if(empty($base_hash)) return false;
        $base_hash = static::get_real_hash($base_hash);
        return $base_hash & ( 1 << $hash - 1 );
    }

    /**
     * 位序统计
     * @param $base_hash
     * @return  array
     */
    public static function resolve_hash($base_hash, $bitmap_size=30)
    {
        $resolved = [];
        $current = 0;
        $max = (1 << $bitmap_size);
        $i = 0;
        while ($max > $current) {
            $current = (1 << $i);
            $resolved[$i] = $current & $base_hash ? 1 : 0;
            $i++;
        }
        return $resolved;
    }

    /**
     * 根据给定的$hash计算之前的连续位点
     * @param $base_hash
     * @param $start
     * @param $hash
     * @return  int
     */
    public static function reverse_search_hash($base_hash, $start = 0, $hash = 0)
    {
        $count = 0;
        if (empty($base_hash)) return $count;
        $base_hash = static::get_real_hash($base_hash);
        $hash_data = static::resolve_hash($base_hash);

        for ($i = $start - 1; $i >= $hash; $i--) {
            if (empty($hash_data[$i])) break;
            $count++;
        }
        return $count;
    }

    /**
     * 解析某个区间的hash值
     * @param $base_hash
     * @param $start
     * @param $end
     * @return array
     */
    public static  function  resolve_values($base_hash, $start, $end)
    {
        $resolved = [];
        if(empty($base_hash)) return $resolved;
        $base_hash = static::get_real_hash($base_hash);
        $hash_data = static::resolve_hash($base_hash);
        for ($i = $start - 1; $i < $end; $i++) {
            if(isset($hash_data[$i])){
                $resolved[$i] = $hash_data[$i];
            } else{
                $resolved[$i] = 0;
            }
        }
        return $resolved;
    }

    /**
     * 追加hash后缀
     * @param $hash
     * @param $append
     * @return float
     */
    public static  function with($hash, $append = 0)
    {
        return sprintf(static::$format, $hash, $append);
    }

    /**
     * 分离hash和后缀
     * @param $hash
     * @return array
     */
    public static function get_real_hash($hash)
    {
        if(false !== strpos($hash, '.')){
            list($base_hash, $append) = explode('.', $hash);
            return intval($base_hash);
        }
        return $hash;
    }

    /**
     *  填充
     * @param $hash_values
     * @param $max
     * @return array
     */
    public static function append($hash_values, $max)
    {
        $length = count($hash_values);
        return array_fill($length, $max - $length + 1, 0);
    }
}
