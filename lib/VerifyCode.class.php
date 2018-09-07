<?php
/**
 * 验证码生成类
 * @author Cary Cui <36086383@qq.com>
 * @copyright (c) 2013-03, 崔俊涛
 * @version V1.0
 *
 * 说明：采用读数据流的方式，读出一张图片，然后直接显示
 */
class VerifyCode {

    //验证码COOKIE调用的名称
    private $verify_name;
    //图片绝对地址
    private $img_path;
    //随机数值
    private $verify_num;

    /**
     * 同一个域名下多次使用该验证码，应该使用不同的名称，即$verify_name的值尽量不一致就不会发生冲突现象
     * @param type $domain 生效域名
     * @param string $verify_name 验证码存在session中的名称
     * @param string $verify_time 是否验证时间，true为验证，默认不验证
     */
    function __construct($domain, $verify_name = 'code', $verify_time = false) {
        $this->verify_name = $verify_name;
        $this->img_path = LIB_ROOT_BASE . '/getimgcode/';
        //制作验证码
        $this->getRand($domain, $verify_time);
    }

    /**
     * 生成图像
     */
    public function getAuthImage() {
        header("Content-type: image/jpeg");
        $im = imagecreatefromjpeg($this->img_path . $this->verify_num . '.jpg');
        imagejpeg($im, NULL, 100);
        imagedestroy($im);
    }

    /**
     * 随机数
     */
    private function getRand($domain,$verify_time) {
        //随机数，可以增加，与$imgCode的元素数量要保持一致
        $this->verify_num = rand(1, 30);

        //验证码数组，上面的rand中的最大值不能超过该数组的下标
        $imgCode = array(
            1 => 'RXJD',
            2 => '2UYH',
            3 => '5HBH',
            4 => '5MDU',
            5 => '6PY3',
            6 => '6UGA',
            7 => '7UXC',
            8 => '8QA2',
            9 => 'BVEM',
            10 => '9ZD4',
            11 => '46Q5',
            12 => 'AHUH',
            13 => 'AKPM',
            14 => 'AZDZ',
            15 => 'BEBH',
            16 => 'B9SK',
            17 => 'BAKG',
            18 => 'BCCZ',
            19 => 'BTDH',
            20 => 'BUFT',
            21 => 'CKF8',
            22 => 'CMTZ',
            23 => 'CSEC',
            24 => 'CSNR',
            25 => 'CTPP',
            26 => 'DAET',
            27 => 'DFYK',
            28 => 'DMGU',
            29 => 'DN4G',
            30 => 'DUWP',
            31 => 'DWXK',
            32 => 'E5YV',
            33 => 'ECTK',
            34 => 'EDVL',
            35 => 'EEDP',
            36 => 'EYK7',
            37 => 'FJ7S',
            38 => 'FJQP',
            39 => 'FKFF',
            40 => 'FLUS',
            41 => 'G6CZ',
            42 => 'HAFJ',
            43 => 'HBTZ',
            44 => 'HVT4',
            45 => 'J3A5',
            46 => 'JCMM',
            47 => 'JTAA',
            48 => 'JWHG',
            49 => 'JYMN',
            50 => 'KQAW',
            51 => 'LHYF',
            52 => 'LKUH',
            53 => 'M8FA',
            54 => 'MLCB',
            55 => 'NBKK',
            56 => 'NDSV',
            57 => 'NLY4',
            58 => 'NQU7',
            59 => 'PMLS',
            60 => 'PQNG',
            61 => 'PSGP',
            62 => 'Q3CR',
            63 => 'Q5XQ',
            64 => 'QCCC',
            65 => 'QCGQ',
            66 => 'QNEQ',
            67 => 'RBBU',
            68 => 'RE7A',
            69 => 'RFUD',
            70 => 'RVDU',
            71 => 'RW4D',
            72 => 'RZGH',
            73 => 'S6MV',
            74 => 'SAV9',
            75 => 'SDU7',
            76 => 'SMG8',
            77 => 'T8LZ',
            78 => 'TAPB',
            79 => 'TGET',
            80 => 'TWVL',
            81 => 'TZ63',
            82 => 'UYRL',
            83 => 'VAJU',
            84 => 'VN8B',
            85 => 'VP8P',
            86 => 'VXLF',
            87 => 'W94E',
            88 => 'WSNP',
            89 => 'WTZU',
            90 => '2U6N',
            91 => 'XDUF',
            92 => 'XEWR',
            93 => 'XFKT',
            94 => 'XYVX',
            95 => 'YEWS',
            96 => 'YXT8',
            97 => 'Z2KM',
            98 => 'ZCBS',
            99 => 'ZFQY',
            100 => 'ZKHR',
            101 => 'ZQWZ',
            102 => 'ZVRS',
            103 => 'ZWGE',
            104 => 'ZYSU',
            105 => 'ZZFA',
            106 => 'YDAGF',
            107 => 'UZKEZ',
            108 => 'KCYMG',
            109 => 'VKPTU',
            110 => 'YAKYW'
        );

        if ($verify_time === false) {
            $cookie_value = $imgCode[$this->verify_num];
        } else {
            $cookie_value = $imgCode[$this->verify_num] . '-' . time();
        }
        CacheCookie::setCookieInfo($this->verify_name, $cookie_value, time() + 1800, '/', $domain);
    }

}
