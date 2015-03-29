<?php
if(!isset($_SESSION)) session_start();
class checkImage {
 
    private $config;
    private $im;
    private $str;
 
    function __construct() {
        $this->config['width']      = 50;
        $this->config['height']     = 20;
        $this->config['vcode']      = "vcode";
        $this->config['type']       = "default";
        $this->config['length']     = 4;
        $this->config['interfere']  = 10;
        $this->str['default']       = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $this->str['string']        = "abcdefghijklmnopqrstuvwxyz";
        $this->str['int']           = "0123456789";
    }
 
    public function init($config=[]){
        if (!empty($config) && is_array($config)){
            foreach($config as $key=>$value){
                $this->config[$key] =   $value;
            }
        }
    }
 
    public function create(){
        if (!function_exists("imagecreate")){
            return false;
        }
        $this->createImage();
    }
 
    private function createImage(){
        $this->im   =   imagecreate($this->config['width'],$this->config['height']);
        imagecolorallocate($this->im, 255, 255, 255);
 
        $bordercolor=   imagecolorallocate($this->im,0,0,0);
        imagerectangle($this->im,0,0,$this->config['width']-1,$this->config['height']-1,$bordercolor);
 
        $this->createStr();
        $vcode  =   $_SESSION[$this->config['vcode']];
        $fontcolor  =   imagecolorallocate($this->im,46,46,46);
        for($i=0;$i<$this->config['length'];$i++){
            imagestring($this->im,5,$i*10+6,rand(2,5),$vcode[$i],$fontcolor);
        }
 
        $interfere  =   $this->config['interfere'];
        $interfere  =   $interfere>30?"30":$interfere;
        if (!empty($interfere) && $interfere>1){
            for($i=1;$i<$interfere;$i++){
                $linecolor  =   imagecolorallocate($this->im,rand(0,255),rand(0,255),rand(0,255));
                $x  =   rand(1,$this->config['width']);
                $y  =   rand(1,$this->config['height']);
                $x2 =   rand($x-10,$x+10);
                $y2 =   rand($y-10,$y+10);
                imageline($this->im,$x,$y,$x2,$y2,$linecolor);
            }
        }
        header("Pragma:no-cachern");
        header("Cache-Control:no-cachern");
        header("Expires:0rn");
        header("content-type:image/jpegrn");
        imagejpeg($this->im);
        imagedestroy($this->im);
        exit;
    }
 
    private function createStr(){
        if ($this->config['type']=="int"){
            for($i=1;$i<=$this->config['length'];$i++){
                $vcode  .=  rand(0,9);
            }
            $_SESSION[$this->config['vcode']] = $vcode;
            return true;
        }
        $len    =   strlen($this->str[$this->config['type']]);
        if (!$len){
            $this->config['type'] = "default";
            $this->create_str();
        }
        for($i=1;$i<=$this->config['length'];$i++){
            $offset  =  rand(0,$len-1);
            $vcode  .=  substr($this->str[$this->config['type']],$offset,1);
        }
        $_SESSION[$this->config['vcode']] = $vcode;
        return true;
    }
 
}
$v = new checkImage();
$config = new array(
	/* 验证码 width */
	'width' 	=> isset($_GET['width']) 		? (int)$_GET['width'] 		: 50,
	/* 验证码 height */
	'height' 	=> isset($_GET['height']) 		? (int)$_GET['height'] 		: 20,
	/* 验证码 session */
	'vcode' 	=> isset($_GET['vcode']) 		? (int)$_GET['vcode'] 		: 'vcode',
	/* 验证码类型 int:数字型 string:小写字母 default:大写字母 */
	'type' 		=> isset($_GET['type']) 		? (int)$_GET['type'] 		: 'int',
	/* 验证码字符的长度 */
	'length' 	=> isset($_GET['length']) 		? (int)$_GET['length'] 		: 4,
	/* 干扰强度 [0,30] 0为不干扰 */
	'interfere' => isset($_GET['interfere']) 	? (int)$_GET['interfere'] 	: 0,
);
$v->init($config);
$v->create();

?>