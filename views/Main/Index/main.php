<?php include PATH_VIEWS . 'header.php' ?>
<?php foreach ($list as $key => $vo) { ?>
    <?php echo $key + 1; ?>、<a href="" class="yui-color-blue"><?php echo $vo['title']; ?></a>
    <hr>
<?php } ?>
<?php include PATH_VIEWS . 'footer.php' ?>