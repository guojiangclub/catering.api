<?php


function build_order_no($prefix = 'O')
{

    //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）

    $order_id_main = date('Ymd') . rand(100000000,999999999);

    //订单号码主体长度

    $order_id_len = strlen($order_id_main);

    $order_id_sum = 0;

    for($i=0; $i<$order_id_len; $i++){

        $order_id_sum += (int)(substr($order_id_main,$i,1));

    }

    //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）

    $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);

    return $prefix.$order_id;
    //return  $prefix. date('Ymd').md5(uniqid(md5(microtime(true)),true));
    //return $prefix . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}