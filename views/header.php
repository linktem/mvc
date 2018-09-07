<!DOCTYPE html>
<html>
    <head>
        <link rel="Shortcut Icon" href="" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <title><?php echo strip_tags(parent::$seo['title']); ?></title>
        <meta name="keywords" content="<?php echo strip_tags(parent::$seo['keywords']); ?>" />
        <meta name="description" content="<?php echo strip_tags(parent::$seo['description']); ?>" />
        <?php foreach (parent::$css_file as $path) { ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $path; ?>" />
        <?php } ?>
        <?php foreach (parent::$js_file_header as $path) { ?>
            <script src="<?php echo $path; ?>" charset="utf-8"></script>
        <?php } ?>
    </head>
    <body>
