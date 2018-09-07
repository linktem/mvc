<?php

/*
 * *****************************************************************************************
 * 基本使用方法:
 * $file = new LibUploadFile();
 * $file->max_size = 100 * 1024; //       //限制文件大小 字节
 * $file->allow_type="gif/jpg/bmp/png"; //允许上传的文件类型
 * $file->input_name="userfile";           //表单中的文本域名称
 * $file->save_path="../uploadfile/";      //保存路径
 * $file->rand_name=false;                   //随机文件名 (默认)
 * $file->reset_name= time();   //重命名
 * if($file->save()) echo "ok"                 //保存文件，成功返回 true
 * ******************************************************************************************
 */

class LibUploadFile {

//变量设置
    var $max_size = 2048000; //允许上传大小，以表单中的MAX_FILE_SIZE优先
    var $time_out = 300;    //脚本超时
    var $allow_type = "rar/zip/jpg/jpeg/png/gif"; //允许上传的文件类型
    var $input_name = ""; //文件域的名称
    var $save_path = ""; //保存路径
    var $reset_name = ""; //重新设置文件名 (优先 rand_name)
    var $rand_name = true; //随机文件名
    //回传变量
    var $file_name = ""; //客户端上传的文件名
    var $file_type = ""; //文件类型
    var $file_size = 0; //大小
    var $file_tmp_name = ""; //服务端临时文件
    var $file_error_txt = ""; //错误提示
    var $file_ext = ""; //扩展名
    var $file_upload_path = ""; //最终的上传文件路径 含文件名 如: uploadfile/filename.rar [注，此处用的是绝对路径。实际项目中可能要做替换操作，才能取到真实文件名]
    var $file_info_array;
    //所有允许上传的文件类型
    var $allow_ext_type = array(//允许上传的扩展名和对应的文件类型
        'dwg' => array('application/octet-stream'),
        'ai' => array('application/postscript'),
        'cdr' => array('application/octet-stream'),
        'mp4' => array('video/mp4'),
        'wav' => array('audio/wav'),
        "avi" => array("video/x-msvideo"),
        "asf" => array("video/x-ms-asf"),
        "bmp" => array("image/bmp", 'application/octet-stream'),
        "css" => array("text/css"),
        "gif" => array("image/gif", 'application/octet-stream'),
        "htm" => array("text/html"),
        "html" => array("text/html"),
        "txt" => array("text/plain"),
        "jpg" => array("image/jpeg", "image/pjpeg", 'application/octet-stream'),
        "jpeg" => array("image/jpeg", "image/pjpeg", 'application/octet-stream'),
        "mp3" => array("audio/mpeg"),
        "pdf" => array("application/pdf"),
        "png" => array("image/png", "image/x-png", 'application/octet-stream'),
        "zip" => array("application/x-zip-compressed","application/zip"),
        "rar" => array("application/octet-stream",'application/rar'),
        "doc" => array("application/octet-stream", 'application/msword','application/octet-stream'),
        'xls' => array('application/vnd.ms-excel','application/octet-stream'),
        'ppt' => array('application/vnd.ms-powerpoint'),
        "swf" => array("application/x-shockwave-flash"),
        'jar' => array('application/x-zip-compressed'),
        'jad' => array('text/plain'),
        'csv' => array('application/vnd.ms-excel'),
        'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/octet-stream'),
        'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/octet-stream'),
        'pptx' => array('application/vnd.openxmlformats-officedocument.presentationml.presentation','application/octet-stream')
    );

    //上传图片默认配置
    function img_config() {
        global $gupload_img_type;
        global $gupload_img_size;
        $this->max_size = $gupload_img_size;
        $this->allow_type = $gupload_img_type;
        $this->save_path = SITE_PATH . "files/uploadfile/" . date("Y-m-d") . "/";
        if (!is_dir($this->save_path)) {
            if (!$this->make_dir($this->save_path)) {
                die("不能创建文件夹: " . $this->save_path);
            }
        }
    }

    //上传附件默认配置
    function file_config($subdir = "") {
        global $gupload_file_type;
        global $gupload_file_size;
        global $gupload_file_dir;
        $this->max_size = $gupload_file_size;
        $this->allow_type = $gupload_file_type;
        if (empty($subdir)) {
            $this->save_path = SITE_PATH . "files/" . $gupload_file_dir . "/" . date("Y-m-d") . "/";
        } else {
            $this->save_path = SITE_PATH . "files/" . $gupload_file_dir . "/" . $subdir . "/";
        }
        if (!is_dir($this->save_path)) {
            if (!$this->make_dir($this->save_path)) {
                die("不能创建文件夹: " . $this->save_path);
            }
        }
    }

    //保存文件
    /* 至少要设置以下两个变量
      var $input_name=""; //文件域的名称
      var $save_path=""; //保存路径
     */
    public function save() {
        //获取文件的信息
        if (!$this->get()) {
            return false;
        }

        //是否有文件的保存路径
        if (empty($this->save_path)) {
            $this->file_error_txt = "请设置文件保存路径";
            return false;
        }

        if (!file_exists($this->save_path)) {
            $this->make_dir($this->save_path);
        }
        $this->save_path = str_replace("//", "/", str_replace("\\", "/", $this->save_path));

        $this->save_path = rtrim($this->save_path, '\/') . "/";
        //保存: 以实际文件名保存
        if (empty($this->reset_name) && !$this->rand_name) {
            $newf = $this->save_path . $this->file_name;
            $this->make_dir($this->save_path);
            if (move_uploaded_file($this->file_tmp_name, $newf)) {
                return true;
            } else {
                $this->file_error_txt = "上传失败1";
                return false;
            }
        }
        //保存:随机文件名
        if (empty($this->reset_name) && $this->rand_name) {
            $newf = $this->save_path . "file_" . $this->get_rand_str(15) . "." . $this->file_ext;
            $this->file_upload_path = $newf;
            $this->make_dir($this->save_path);
            if (move_uploaded_file($this->file_tmp_name, $newf)) {
                return true;
            } else {
                $this->file_error_txt = "上传失败2";
                return false;
            }
        }
        //保存:以重新设置的文件名
        if (!empty($this->reset_name)) {
            $newf = $this->save_path . $this->reset_name . "." . $this->file_ext;
            $this->file_upload_path = $newf;
            $this->make_dir($this->save_path);
            if (move_uploaded_file($this->file_tmp_name, $newf)) {
                return true;
            } else {
                $this->file_error_txt = "上传失败3";
                return false;
            }
        }
        //完成.
    }

    //获得并判断上传文件的信息
    function get() {
        if (empty($this->input_name)) {
            $this->file_error_txt = "please set the file inputname";
            return false;
        }

        if ((int) $this->time_out > 0) {
            set_time_limit($this->time_out);
        }
        //上传的文件信息
        $fobj = $_FILES[$this->input_name];
        $this->file_info_array = $fobj;
        $this->file_name = isset($fobj["oldName"]) ? $fobj["oldName"] : $fobj["name"];
        $this->file_type = $fobj["type"];
        $this->file_size = $fobj["size"];
        $this->file_tmp_name = $fobj["tmp_name"];
        $file_error = $fobj["error"];

        //判断文件错误代码
        if (!$this->check_error_code($file_error)) {
            return false;
        }

        //判断文件是否是通过 HTTP POST 上传的
        if (!is_uploaded_file($this->file_tmp_name)) {
            $this->file_error_txt = "Please upload file from HTTP POST method.";
            return false;
        }

        //检测文件大小
        if ($this->file_size > $this->max_size) {
            $this->file_error_txt = "上传文件超过最大限制:" . (int) ($this->max_size / 1024) . "KB";
            return false;
        }

        //检测文件是否有名
        if (empty($this->file_name)) {
            $this->file_error_txt = "need file name.";
            return false;
        }

        //检测文件的扩展名(有时候文件没有后缀名，但确实是一副图片)
        //$ext = strpos($this->file_name, ".");
        //if ($ext === false) {
            //$this->file_error_txt = "file ext is wrong";
            //return false;
        //}
//        $ext = strtolower(end(explode('.',$this->file_name)));
        //$ext = strtolower((string) substr($this->file_name, $ext + 1, strlen($this->file_name) - $ext));
        //$ext = strtolower(substr($this->file_name, strpos($this->file_name, '.') + 1));
        //如果是发送的截图
        if ($this->file_name == 'blob') {
            $ext = 'png';
        } else {
            $ext_arr = explode('.', $this->file_name);
            if (count($ext_arr) < 2) {
                $ext = 'jpg';
            } else {
                $ext = strtolower(end($ext_arr));
            }
        }
        $this->file_ext = $ext;
        if (strpos($this->allow_type, $ext) === false) {
            $this->file_error_txt = "*." . $ext . " is not allowed to upload";
            return false;
        }

        //检测文件类型
        if (!isset($this->allow_ext_type[$ext])) {
            $this->file_error_txt = "the file type of *." . $ext . " not allowed";
            return false;
        } else {
            if (!in_array($this->file_type, $this->allow_ext_type[$ext])) {
                $this->file_error_txt = "当前文件类型: ." . $ext . " 不是一个被支持的标准文件类型:" . $this->allow_ext_type[$ext][0];
                return false;
            }
        }

        //基本上可以上传
        return true;
    }

    //判断错误代码
    function check_error_code($code) {
        switch ($code) {
            case 0:
                $this->file_error_txt = "";
                return true;
                break;
            case 1:
                $this->file_error_txt = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                return false;
                break;
            case 2:
                $this->file_error_txt = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                return false;
                break;
            case 3:
                $this->file_error_txt = "The uploaded file was only partially uploaded.";
                return false;
                break;
            case 4:
                $this->file_error_txt = "No file was uploaded.";
                return false;
                break;
            case 6:
                $this->file_error_txt = "Missing a temporary folder.";
                return false;
                break;
            case 7:
                $this->file_error_txt = "Failed to write file to disk.";
                return false;
                break;
            case 8:
                $this->file_error_txt = "File upload stopped by extension.";
                return false;
                break;
            default:
                $this->file_error_txt = "unknow error.";
                return false;
                break;
        }
    }

    //得到随机字符串
    function get_rand_str($length) {
        $hash = "";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($chars) - 1;
        mt_srand((double) microtime() * 1000000);
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    //建立多层目录
    function make_dir($file_path, $mode = 0777) {
        if (empty($file_path)) {
            return false;
        }
        $dir_path_arr = explode('/', $file_path);
        //array_push($dir_path_arr);
        $dir = implode('/', $dir_path_arr);
        if (file_exists($dir)) {
            return false;
        }
        $dir = str_replace('//', '/', $dir);
        $folderArray = explode("/", $dir);
        $folder = "";
        foreach ($folderArray as $key => $folderOne) {
            $folder .= '/' . $folderOne;
            if (!file_exists($folder)) {
                @mkdir($folder);
                @chmod($folder, $mode);
            }
        }
        return true;
    }

}
