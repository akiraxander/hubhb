<?php

/**
 *  パンくずリスト
 *  <?php if (function_exists('breadcrumb')) { breadcrumb(); }?>
 *
 *  @param {string} $classes class名を追加
 *  @param {string} $home_name TOPページの名前（デフォルトでは 「サイト名 - TOP」）
 */
if (!function_exists('breadcrumb')) {
    function breadcrumb($classes = '', $home_name = '')
    {
        global $call;

        $path = '';
        $separate = "<span class=\"breadcrumb-item separate\">&gt;</span>\n";
        $crumbs = array_filter(explode('/', trim($call, '/')));

        if ($home_name === '') {
            $home_name = get_bloginfo("name") . ' - Top';
        }
?>
<nav class="breadcrumb breadcrumb-container<?php echo ($classes === '' ? '' : ' ' . $classes); ?>" role="navigation" aria-label="Breadcrumbs"><div class="breadcrumb-wrapper">
    <span class="breadcrumb-item">
        <a href="<?php echo esc_attr(home_url('/')); ?>"><?php echo esc_html($home_name); ?></a>
    </span>
    <?php
        $level = count($crumbs);
        for ($i = 1; $i <= $level; ++$i) {
            $path .= '/' . $crumbs[$i - 1];
    ?>
    <?php echo $separate; ?>
    <span class="breadcrumb-item current">
        <?php if ($i == $level) {$hierarchy = '';?>
            <span><?php the_title($path); ?></span>
        <?php } else {?>
            <a href="<?php echo esc_attr(get_permalink($path)); ?>"><?php the_title($path); ?></a>
        <?php }?>
    </span>
    <?php }?>
</div></nav>
<?php
    }
}