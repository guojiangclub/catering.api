<?php

/**
 * 生成商品货号
 *
 * @return string
 */
function createGoodsNo()
{
	return 'SD' . time() . rand(10, 99);
}

function multi_unique($array)
{
	foreach ($array as $k => $v) {
		$new = [];
		foreach ($v as $key => $val) {
			$new[$key] = serialize($val);
		}
		$uniq[$k] = array_unique($new);
	}

	foreach ($uniq as $key => $val) {
		$newArr = [];
		foreach ($val as $j => $c) {
			$newArr[$j] = unserialize($c);
		}

		$newArray[$key] = $newArr;
	}

	return $newArray;
}

/**
 * 数组转对象
 *
 * @param $obj
 *
 * @return mixed
 */
function objectToArray($obj)
{
	$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
	foreach ($_arr as $key => $val) {
		$val       = (is_array($val) || is_object($val)) ? objectToArray($val) : $val;
		$arr[$key] = $val;
	}

	return $arr;
}

function filterTextArea($val)
{
	$string = str_replace("\n", ",", $val);
	$string = str_replace("\r\n", ",", $string);

	$string = str_replace(' ', '', $string);
	$data   = explode(',', $string);
	foreach ($data as $key => $item) {
		if (empty($item)) {
			unset($data[$key]);
		}
	}

	return implode(',', $data);
}

function showTextArea($val)
{
//    $string = str_replace(",","\n",$val);
//    return nl2br($string);
	$string = explode(',', $val);
	//return nl2br($string);
	$tmp = '';
	foreach ($string as $val) {
		$tmp .= $val . '&#13;&#10;';
	}

	return $tmp;
}

/*
*功能：php完美实现下载远程图片保存到本地
*参数：文件url,保存文件目录,保存文件名称，使用的下载方式
*当保存文件名称为空时则使用远程文件原来的名称
*/
function getImage($url, $save_dir = '', $filename = '', $type = 0)
{
	if (trim($url) == '') {
		return ['file_name' => '', 'save_path' => '', 'error' => 1];
	}
	if (trim($save_dir) == '') {
		$save_dir = './';
	}
	if (trim($filename) == '') {//保存文件名
		$ext = strrchr($url, '.');
		if ($ext != '.gif' && $ext != '.jpg') {
			return ['file_name' => '', 'save_path' => '', 'error' => 3];
		}
		$filename = time() . $ext;
	}
	if (0 !== strrpos($save_dir, '/')) {
		$save_dir .= '/';
	}
	//创建保存目录
	if (!file_exists($save_dir) && !mkdir($save_dir, 0755, true)) {
		return ['file_name' => '', 'save_path' => '', 'error' => 5];
	}
	//获取远程文件所采用的方法
	if ($type) {
		$ch      = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$img = curl_exec($ch);
		curl_close($ch);
	} else {
		ob_start();
		readfile($url);
		$img = ob_get_contents();
		ob_end_clean();
	}
	//$size=strlen($img);
	//文件大小
	$fp2 = @fopen($save_dir . $filename, 'a');
	fwrite($fp2, $img);
	fclose($fp2);
	unset($img, $url);

	return ['file_name' => $filename, 'save_path' => $save_dir . $filename, 'error' => 0];
}

/**
 * 将图片转换成圆形
 *
 * @param        $url    图片路径或URL
 * @param        $width  宽度
 * @param        $height 高度
 * @param string $path   保存路径
 *
 * @return string
 */
function circularImg($url, $path = './', $width = 200, $height = 200)
{
	$w = $width;
	$h = $height; // original size

	$original_path = $url;
	$dest_path     = $path;
	$src           = imagecreatefromstring(file_get_contents($original_path));
	$newpic        = imagecreatetruecolor($w, $h);
	imagealphablending($newpic, false);
	$transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
	$r           = $w / 2;
	for ($x = 0; $x < $w; $x++) {
		for ($y = 0; $y < $h; $y++) {
			$c  = imagecolorat($src, $x, $y);
			$_x = $x - $w / 2;
			$_y = $y - $h / 2;
			if ((($_x * $_x) + ($_y * $_y)) < ($r * $r)) {
				imagesetpixel($newpic, $x, $y, $c);
			} else {
				imagesetpixel($newpic, $x, $y, $transparent);
			}
		}
	}
	imagesavealpha($newpic, true);
	imagepng($newpic, $dest_path);
	imagedestroy($newpic);
	imagedestroy($src);

	// unlink($url);
	return $dest_path;
}

function get_lt_rounder_corner($radius)
{
	$img     = imagecreatetruecolor($radius, $radius);  // 创建一个正方形的图像
	$bgcolor = imagecolorallocate($img, 255, 255, 255);   // 图像的背景
	$fgcolor = imagecolorallocate($img, 0, 0, 0);
	imagefill($img, 0, 0, $bgcolor);
	// $radius,$radius：以图像的右下角开始画弧
	// $radius*2, $radius*2：已宽度、高度画弧
	// 180, 270：指定了角度的起始和结束点
	// fgcolor：指定颜色
	imagefilledarc($img, $radius, $radius, $radius * 2, $radius * 2, 180, 270, $fgcolor, IMG_ARC_PIE);
	// 将弧角图片的颜色设置为透明
	imagecolortransparent($img, $fgcolor);
	// 变换角度
	// $img = imagerotate($img, 90, 0);
	// $img = imagerotate($img, 180, 0);
	// $img = imagerotate($img, 270, 0);
	// header('Content-Type: image/png');
	// imagepng($img);
	return $img;
}

function getWechatHeader($headimgurl, $user_id)
{
	$ch = curl_init($headimgurl);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Encoding:gzip']);
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);

	$path = storage_path('app/public/uploads/' . $user_id . '_wx.jpg');
	file_put_contents($path, $output); // 写入文件
}

/*生成线下优惠券码*/
function createOfflineCouponCode($prefix = '91630', $start = '30001', $end = '40000')
{
	$numbers = rand($start, $end);

	if (strlen($numbers) < 5) {

		$numbers = str_pad($numbers, 8, "0", STR_PAD_LEFT);
		//return $prefix . substr($numbers, -5);
		$code = $prefix . substr($numbers, -5);
	} else {
		$code = $prefix . $numbers;
	}

	$couponCode = app(\GuoJiangClub\Catering\Backend\Models\Coupon\Coupon::class);
	if ($couponCode->where('code', $code)->first()) {
		return createOfflineCouponCode($prefix, $start, $end);
	}

	return $code;
	//return $prefix . $numbers;

}

if (!function_exists('getLastMonthArea')) {
	/**
	 * 获取最近12个月
	 *
	 * @param     $year
	 * @param     $month
	 * @param     $length
	 * @param int $page
	 *
	 * @return array
	 */
	function getLastMonthArea($year, $month, $length, $page = 1)
	{
		if (!$page) {
			$page = 1;
		}
		$monthNum = $month + $length - $page * $length;
		$num      = 1;
		if ($monthNum < -12) {
			$num = ceil($monthNum / (-12));
		}

		$timeAreaList = [];
		for ($i = 0; $i < $length; $i++) {
			$temMonth = $monthNum - $i;
			$temYear  = $year;
			if ($temMonth <= 0) {
				$temYear  = $year - $num;
				$temMonth = $temMonth + 12 * $num;
				if ($temMonth <= 0) {
					$temMonth += 12;
					$temYear  -= 1;
				}
			}

			//该月的月初时间戳
			$startMonth = strtotime($temYear . '-' . $temMonth . '-01');
			if ($temMonth + 1 > 12) {
				//该月的月末时间戳
				$endMonth = strtotime($temYear + 1 . '-01' . '-01 23:59:59') - 86400;
			} else {
				//该月的月末时间戳
				$endMonth = strtotime($temYear . '-' . ($temMonth + 1) . '-01 23:59:59') - 86400;
			}

			//该月的月初格式化时间
			$res['startMonth'] = $temYear . '-' . $temMonth . '-01 00:00:00';
			//该月的月末格式化时间
			$res['endMonth']     = date('Y-m-d H:i:s', $endMonth);
			$res['currentMonth'] = date('Y-m', $endMonth);
			//区间时间戳
			$res['timeArea'] = implode(',', [$startMonth, $endMonth]);
			$timeAreaList[]  = $res;
		}

		return $timeAreaList;
	}
}

if (!function_exists('getLastDayArea')) {
	/**
	 * 获取最近30天
	 *
	 * @return array
	 */
	function getLastDayArea()
	{
		$day      = [];
		$day_time = [];
		for ($i = 30; $i >= 1; $i--) {
			$day[]      = date('n-d', time() - 60 * 60 * 24 * $i);
			$day_time[] = date('Y-m-d', time() - 60 * 60 * 24 * $i);
		}

		return [$day, $day_time];
	}
}