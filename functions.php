<?php
/**
 * Functions.php contains all the core functions for your theme to work properly.
 *
 * @package dangopress
 */

if (is_admin()) {
    require_once('theme-options.php');
}

/*
 * Check whether the user is visiting in mobile device
 */
define('IS_MOBILE', wp_is_mobile());

/*
 * Require widgets functions
 */
require_once('functions/widgets.php');

/*
 * Include custom function php file if exists.
 *
 * You should put your personal functions in the functions/custom.php.
 */
define('CUSTOM_FUNCTIONS', get_template_directory() . '/functions/custom.php');

if (file_exists(CUSTOM_FUNCTIONS)) {
    require_once(CUSTOM_FUNCTIONS);
}

/**
 * Set the content width based on the theme's design and stylesheet.
 */
$content_width = 640;

/*
 * Customize filter and actions
 */
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'locale_stylesheet');
remove_action('wp_head', 'noindex', 1);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

/*
 * Disable xml rpc
 */
add_filter('xmlrpc_enabled', '__return_false');

/*
 * Disable Automatic Formatting
 */
remove_filter('the_content', 'wptexturize');
remove_filter('the_excerpt', 'wptexturize');
remove_filter('the_title', 'wptexturize');
remove_filter('comment_text', 'wptexturize');

/*
 * Customize wordpress title
 */
function dangopress_wp_title($sep)
{
    $search = get_query_var('s');

    if (is_front_page() || is_home()) {  // home or front page
        $blog_name = get_bloginfo('name');
        $site_description = get_bloginfo('description');

        if ($site_description) {
            echo "$blog_name $sep $site_description";
        } else {
            echo "$blog_name";
        }
    } else if (is_single() || is_page()) { // singular page
        single_post_title('', true);
    } else if (is_category()) { // category page
        printf('%1$s 类目的文章存档', single_cat_title('', false));
    } else if (is_search()) { // search page
        printf('%1$s 的搜索结果', strip_tags($search));
    } else if(is_tag()) { // tag page
        printf('%1$s 标签的文章存档', single_tag_title('', false));
    } else if(is_date()) { // date page
        if (is_day()) {
            $title = get_the_time('Y年n月j日');
        } else if(is_year()) {
            $title = get_the_time('Y年');
        } else {
            $title = get_the_time('Y年n月');
        }

        printf('%1$s的文章存档', $title);
    } else { // other page
        bloginfo('name');
    }
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function dangopress_setup_theme()
{
    // Add theme support
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-background');
    add_theme_support('post-thumbnails');

    // Register wordpress menu
    register_nav_menus(array('primary' => 'Primary Navigation'));

    // Register sidebars for use in this theme
    register_sidebar(array(
        'name' => 'Sidebar',
        'id' => 'sidebar',
        'description' => '该区域的小工具会显示在右方的侧栏中',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ));

    // Register auto-followed sidebar
    register_sidebar(array(
        'name' => 'Sidebar Follow',
        'id' => 'sidebar-follow',
        'description' => '该区域的小工具会显示在右方侧栏的跟随部分中',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ));
}
add_action('after_setup_theme', 'dangopress_setup_theme');

/*
 * Get theme url prefix for styles or scripts
 */
function dangopress_get_url_prefix()
{
    $options = get_option('dangopress_options');

    if (!empty($options['cdn_prefix']))
        return $options['cdn_prefix'];
    else
        return get_template_directory_uri();
}

/*
 * Load css and javascript
 */
function dangopress_setup_load()
{
    // URL prefix
    $url_prefix = dangopress_get_url_prefix();

    // Main css
    wp_enqueue_style('style', get_stylesheet_uri());

    // Font awesome css
    wp_enqueue_style('font-awesome', $url_prefix . '/styles/font-awesome.min.css', array(), '3.2.1');

    // Replace jQuery, use Baidu Public Library CDN
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', "http://libs.baidu.com/jquery/2.0.3/jquery.min.js",
                           array(), '2.0.3', true);
    }

    // Register prettify.js
    wp_enqueue_script('prettify-js', $url_prefix . '/scripts/prettify.min.js',
                       array(), '20130504', true);

    // Theme script
    wp_enqueue_script('kodango', $url_prefix . '/scripts/kodango.min.js',
                      array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'dangopress_setup_load');

/*
 * Enqueue comemnt-reply.js in the footer
 */
function dangopress_enqueue_comment_reply()
{
    // Thread comments
	if (is_singular() && comments_open()) // && get_option('thread_comments'))
        wp_enqueue_script('comment-reply');
}
add_action('comment_form_before', 'dangopress_enqueue_comment_reply');

/*
 * Wrap the post image in div container
 */
if (is_admin()) {
    function dangopress_wrap_post_image($html, $id, $caption, $title, $align, $url, $size, $alt)
    {
        return '<div class="post-image">'.$html.'</div>';
    }
    add_filter('image_send_to_editor', 'dangopress_wrap_post_image', 10, 8);
}

/*
 * Add rel="nofollow" to read more link
 */
function dangopress_nofollow_link($link)
{
    return str_replace('<a', '<a rel="nofollow"', $link);
}
add_filter('the_content_more_link', 'dangopress_nofollow_link', 0);

/*
 * Disable self ping
 */
function dangopress_disable_self_ping(&$links)
{
    $home = get_option('home');

    foreach ($links as $l => $link)
        if (0 === strpos($link, $home))
            unset($links[$l]);
}
add_action('pre_ping', 'dangopress_disable_self_ping');

/*
 * Remove version number in the loading script or stylesheet
 */
function dangopress_remove_version($src)
{
    $parts = explode('?ver', $src);
    return $parts[0];
}
add_filter('script_loader_src', 'dangopress_remove_version', 15, 1);
add_filter('style_loader_src', 'dangopress_remove_version', 15, 1);

/*
 * Escape special characters in pre.prettyprint into their HTML entities
 */
function dangopress_esc_html($content)
{
    $patterns = array(
        '/(<pre\s+[^>]*?class\s*?=\s*?[",\'].*?prettyprint.*?[",\'].*?>)(.*?)(<\/pre>)/sim',
        '/(<pre>[\n\s]*<code>)(.*?)(<\/code>[\n\s]*<\/pre>)/sim',
    );

    return preg_replace_callback($patterns, dangopress_esc_callback, $content);
}

function dangopress_esc_callback($matches)
{
    $tag_open = $matches[1];
    $content = $matches[2];
    $tag_close = $matches[3];

    //$content = htmlspecialchars($content, ENT_NOQUOTES, get_bloginfo('charset'));
    $content = esc_html($content);
    $tag_open = preg_replace('/<pre>[\n\s]*<code>/', '<pre class="prettyprint"><code>', $tag_open);

    return $tag_open . $content . $tag_close;
}
add_filter('the_content', 'dangopress_esc_html', 2);
add_filter('comment_text', 'dangopress_esc_html', 2);

/*
 * Alter the main loop
 */
function dangopress_alter_main_loop($query)
{
    /* Only for main loop in home page */
    if (!$query->is_home() || !$query->is_main_query())
        return;

    // ignore sticky posts, don't show them in the start
    $query->set('ignore_sticky_posts', 1);
}
add_action('pre_get_posts', 'dangopress_alter_main_loop');

/*
 * Retrieve paginated link for archive post pages
 */
function dangopress_paginate_links()
{
    global $wp_query;

    $total = $wp_query->max_num_pages;
    $big = 999999999; // need an unlikely integer

    if ($total < 2)
        return;

    $output = paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $total,
        'prev_text' => '<i class="icon-circle-arrow-left"></i>',
        'next_text' => '<i class="icon-circle-arrow-right"></i>',
    ));

    echo '<div id="post-pagenavi">' . $output . '</div>';
}

/*
 * Show humanable time delta
 */
function dangopress_human_time_diff($gmt_time)
{
    $from_timestamp = strtotime("$gmt_time" . ' UTC');
    $to_timestamp = current_time('timestamp', 1);

    if ($to_timestamp - $from_timestamp > 2592000) { // One month ago
        return date_i18n('Y-m-d G:i:s', $from_timestamp, true);
    } else {
        $diff = human_time_diff($from_timestamp, $to_timestamp);
        return preg_replace('/(\d+)/', "$1 ", "{$diff}前");
    }
}

/*
/*
 * Display comment lists
 */
function dangopress_comments_callback($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;

    global $commentcount;

    /* Initialize the comment count */
    if (!$commentcount) {
        $page = get_query_var('cpage') - 1;

        if ($page > 0) {
            $cpp = get_option('comments_per_page');
            $commentcount = $cpp * $page;
        } else {
            $commentcount = 0;
        }
    }

    $comment_id = $comment->comment_ID; ?>

    <li <?php comment_class(); ?> id="li-comment-<?php echo $comment_id; ?>">
        <div id="comment-<?php echo $comment_id; ?>" class="comment-body <?php if ($comment->comment_approved == '0') echo 'pending-comment'; ?>">
            <div class="comment-avatar">
                <?php echo get_avatar($comment, $depth==1 ? 48: 32, '', "$comment->comment_author's avatar"); ?>
            </div>
            <div class="comment-meta">
                <span class="comment-author<?php echo user_can($comment->user_id, 'administrator') ? ' postauthor': ''?>"><?php comment_author_link(); ?></span>
                <span class="comment-date">发表于 <?php echo dangopress_human_time_diff($comment->comment_date_gmt); ?></span>
                <span><i class="icon-retweet"></i>
                <?php
                    comment_reply_link(array_merge($args, array(
                        'reply_text' => '回复',
                        'depth' => $depth,
                        'max_depth' => $args['max_depth']
                    )));

                    if ($depth == 1) { // Show the floor number
                         printf(' %1$s 楼', ++$commentcount);
                    }
                ?>

                </span>

            </div>

            <div class="comment-text"><?php comment_text(); ?></div>
        </div>
<?php
}

/*
 * Add at user before comment text
 */
function dangopress_add_at_author($comment_text, $comment)
{
    if ($comment->comment_parent) { // Show reply to somebody
        $parent = get_comment($comment->comment_parent);
        $parent_href = htmlspecialchars(get_comment_link($comment->comment_parent));

        $parent_author = $parent->comment_author;
        $parent_title = mb_strimwidth(strip_tags($parent->comment_content), 0, 100, '...');

        $parent_link = "<a href=\"$parent_href\" title=\"$parent_title\">@$parent_author</a>";
        $comment_text = '<span class="at-author">' . $parent_link . ':</span>' . $comment_text;
    }

    return $comment_text;
}
add_filter('comment_text', 'dangopress_add_at_author', 10, 2);

/*
 * Check whether a gravatar exists for a given user email
 *
 * Taken from gist: https://gist.github.com/justinph/5197810
 */
function dangopress_validate_gravatar($email)
{
    if (empty($email))
        return false;

    $hashkey = md5(strtolower(trim($email)));
    $uri = 'http://www.gravatar.com/avatar/' . $hashkey . '?d=404';

    $data = wp_cache_get($hashkey);

    if ($data === false) {
        $response = wp_remote_head($uri);

        if (is_wp_error($response)) {
            $data = 'not200';
        } else {
            $data = $response['response']['code'];
        }

        wp_cache_set($hashkey, $data, $group = '', $expire = 60*60*24);
    }

    if ($data == '200') {
        return true;
    } else {
        return false;
    }
}

/*
 * Get rid of spam comments
 */
function dangopress_getridof_spam($commentdata)
{
    extract($commentdata, EXTR_SKIP);
    $nonce = wp_create_nonce($comment_post_ID);

    /* Check whether the user is in the blacklist */
    if (wp_blacklist_check($comment_author, $comment_author_email, $comment_author_url,
            $comment_content, $comment_author_IP, $comment_agent)) {
        $msg = '您发表的评论中包含被禁止的关键字, 请检查后再评论';
        $msg .= ', 点击<a href="' . $_SERVER['HTTP_REFERER'] . '">此处</a>返回';

        wp_die($msg);
    }

    /* Check whether the comment is pingback or trackback */
    $is_ping = in_array($comment_type, array('pingback', 'trackback'));

    if (!is_ping) { // For normal comments
        /* Check the rand nonce strings */
        if (!isset($_POST['comment_nonce']) || $_POST['comment_nonce'] != $nonce) {
            wp_die('请勿以非正常的方式进行评论');
        }
    }

    /* Check whether the comment text contains the japanese chars */
    if (preg_match('/[ぁ-ん]+|[ァ-ヴ]+/u', $comment_content) ||
            preg_match('/[ぁ-ん]+|[ァ-ヴ]+/u', $comment_author)) {
        wp_die('请勿恶意评论');
    }

    return $commentdata;
}
add_action('preprocess_comment', 'dangopress_getridof_spam');

/*
 * Tag spam comments
 */
function dangopress_tag_spam($approved, $commentdata)
{
    extract($commentdata, EXTR_SKIP);

    /* Check whether the comment is pingback or trackback */
    $is_ping = in_array($comment_type, array('pingback', 'trackback'));

    /* Parse the author url domain part */
    $author_domain = parse_url($comment_author_url, PHP_URL_PATH);

    /* For pingback/trackback, check the comment author domain length */
    if ($is_ping && strlen($domain) > 25) {
        return 'spam';
    }

    /* For normal comment, check the comment author url length */
    if (!$is_ping && strlen($comment_author_url) > 25) {
        return 'spam';
    }

    /* Check whether there is any chinese words exists */
    $has_chinese = preg_match('/[\x{4e00}-\x{9fa5}]+/u', $comment_content);

    /* Check the comment chars length */
    $comment_chars = strlen(trim($comment_content));

    /*
     * Say too many words without any chinese, tag it spam!
     */
    if (!$has_chinese && $comment_chars > 80) {
        return 'spam';
    }

    if (!$is_ping) { // Normal comment
        /* Check whether the gravatar exists */
        $has_gravatar = dangopress_validate_gravatar($comment_author_email);

        /*
         * For normal comment without a gravatar:
         *
         * Tag it as spam if no chinese words found, or comment characters are
         * too many. It's more strict than bellow.
         *
         * One chinese character in UTF-8 takes up 3 bytes
         */
        if (!$has_gravatar) {
            if (!$has_chinese || $comment_chars > 120) {
                return 'spam';
            } else {
                return 0; // disapproved
            }
        }
    }

    return $approved;
}
add_filter('pre_comment_approved', 'dangopress_tag_spam', 99, 2);

/*
 * Open the link in the new tab
 */
function dangopress_new_tab($link)
{
    return str_replace('<a', '<a target="_blank"', $link);
}
add_filter('get_comment_author_link', 'dangopress_new_tab');

/*
 * Send an email when recieved a reply
 */
function dangopress_email_nodify($comment_id)
{
    global $wpdb;

    $admin_email = get_bloginfo('admin_email');

    $comment = get_comment($comment_id);
    $comment_author_email = trim($comment->comment_author_email);

    $parent_id = $comment->comment_parent ? $comment->comment_parent : '';

    /*
     * Add comment_mail_notify column when first run
     */
    if ($wpdb->query("Describe $wpdb->comments comment_mail_notify") == '')
        $wpdb->query("ALTER TABLE $wpdb->comments ADD COLUMN comment_mail_notify TINYINT NOT NULL DEFAULT 0;");

    /*
     * Set notify value to 1 if the checkbox is checked in the comment form
     */
    if (isset($_POST['comment_mail_notify']))
        $wpdb->query("UPDATE $wpdb->comments SET comment_mail_notify='1' WHERE comment_ID='$comment_id'");

    $notify = $parent_id ? get_comment($parent_id)->comment_mail_notify : '0';
    $spam_confirmed = $comment->comment_approved;

    /*
     * Don't send email if:
     * 1. the comment is a spam
     * 2. the notify checkbox isn't checked
     */
    if ($notify != '1' || $spam_confirmed == 'spam')
        return;

    // Prepare the email
    $sender = 'no-reply@' . preg_replace('#^www.#', '', strtolower($_SERVER['SERVER_NAME']));

    $to = trim(get_comment($parent_id)->comment_author_email);
    $subject = '您在 [' . get_option('blogname') . '] 的留言有了回复';

    $from = 'From: "' . get_option('blogname') . '" <' . $sender . '>';
    $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";

    $message .= '<div style="background-color:#eef2fa;border:1px solid #d8e3e8;padding:0 15px;">';
    $message .= '<p style="color:#000">您好, <strong>' . trim(get_comment($parent_id)->comment_author) . '</strong>:</p>';
    $message .= '<p style="color:#000">您曾在《' . get_the_title($comment->comment_post_ID) . '》的留言: </p>';
    $message .= '<blockquote style="background:#fafafa;border-left:1px solid #ddd;padding:10px;margin:15px 0;">';
    $message .= trim(get_comment($parent_id)->comment_content) . '</blockquote>';
    $message .= '<p style="color:#000">收到来自 <strong>' . trim($comment->comment_author) . '</strong> 给您的回复:</p>';
    $message .= '<blockquote style="background:#fafafa;border-left:1px solid #ddd;padding:10px;margin:15px 0;">';
    $message .= trim($comment->comment_content) . '</blockquote>';
    $message .= '<p style="color:#000">您可以点击以下链接（或者复制链接到地址栏访问）查看回复的完整内容:</p>';
    $message .= '<blockquote style="background:#fafafa;border-left:1px solid #ddd;padding:10px;margin:15px 0;">';
    $message .= get_comment_link($comment) . '</blockquote>';
    $message .= '<p style="color:#000">欢迎再次光临 <a href="' . get_bloginfo('url') . '">' . get_bloginfo('name') . '</a></p>';
    $message .= '<p style="color:#888;">友情提醒: 此邮件由系统自动发送，请勿回复。</p></div>';

    wp_mail($to, $subject, $message, $headers);
}
add_action('comment_post', 'dangopress_email_nodify');

/*
 * Show breadcrumb by yoast breadcrumb plugin
 */
function dangopress_breadcrumb()
{
    //if (!function_exists('yoast_breadcrumb') || is_home() || is_page())
    if (!function_exists('yoast_breadcrumb') || is_home())
        return;

    yoast_breadcrumb('<div id="site-breadcrumbs">', '</div>');
}

/*
 * Number the breadcrumb links
 */
function dangopress_number_breadcrumbs($links)
{
    $my_links = array();

    $count = count($links);
    $index = 1;

    foreach ($links as $key => $value) {
        $value['my_index'] = $index++;
        $value['my_total_count'] = $count;

        $my_links[] = $value;
    }

    return $my_links;
}
add_filter('wpseo_breadcrumb_links', 'dangopress_number_breadcrumbs', 10, 1);

/*
 * Customize breadcrumb links
 */
function dangopress_customize_breadcrumb($link_output, $link)
{
    $index = $link['my_index'];
    $total_count = $link['my_total_count'];

    /*
    if ($index == 1) {
        // no follow the home link
        $link_output = dangopress_nofollow_link($link_output);
    } else if ((is_archive() || is_search()) && $index == $total_count) {
        // surround <h1> for the last element in archive or search page
        //$link_output = '<h1>' . $link_output . '</h1>';
        $link_output = preg_replace(array('/<span /', '/<\/span>/'), array('<h1 ', '</h1>'), $link_output);
    } else if ((is_single()) && $index == ($total_count-1)) {
        // surround <h2> for the post category in single post page
        //$link_output = '<h2>' . $link_output . '</h2>';
        $link_output = preg_replace(array('/<span /', '/<\/span>/'), array('<h2 ', '</h2>'), $link_output);
    } else if (is_single() && $index == $total_count) {
        // remove post title from the breadcrumbs
        $link_output = str_replace($link['text'], '当前位置', $link_output);
        //$link_output = preg_replace(array('/<span /', '/<\/span>/'), array('<h1 ', '</h1>'), $link_output);
    }
 */

    if ($index == 1) {
        // no follow the home link
        $link_output = dangopress_nofollow_link($link_output);
    } else if ($index == $total_count) {
        // surround <h1> for the last element
        //$link_output = '<h1>' . $link_output . '</h1>';
        $link_output = preg_replace(array('/<span /', '/<\/span>/'), array('<h1 ', '</h1>'), $link_output);
    } else if ($index == ($total_count-1) && isset($link['term'])) {
        // surround <h2> for the post category
        //$link_output = '<h2>' . $link_output . '</h2>';
        $link_output = preg_replace(array('/<span /', '/<\/span>/'), array('<h2 ', '</h2>'), $link_output);
    }

    return $link_output;
}
add_filter('wpseo_breadcrumb_single_link', 'dangopress_customize_breadcrumb', 10, 2);

/*
 * Place baidu share icons
 */
function dangopress_place_bdshare()
{
    $options = get_option('dangopress_options');

    if (empty($options['bdshare_uid']))
        return;
?>

<div id="bdshare" class="bdshare_t bds_tools_24 get-codes-bdshare">
    <a class="bds_tsina"></a>
    <a class="bds_tqq"></a>
    <a class="bds_twi"></a>
    <a class="bds_hi"></a>
    <a class="bds_douban"></a>
    <a class="bds_tieba"></a>
    <a class="bds_youdao"></a>
    <a class="bds_copy"></a>
    <span class="bds_more"></span>
</div>

<?php

    add_action('wp_footer', 'dangopress_load_bdshare');
}

/*
 * Load baidu share scripts
 */
function dangopress_load_bdshare()
{
    $options = get_option('dangopress_options');
    $bdshare_uid = $options['bdshare_uid'];
?>

<script type="text/javascript" id="bdshare_js" data="type=tools&amp;uid=<?php echo $bdshare_uid; ?>" ></script>
<script type="text/javascript" id="bdshell_js"></script>
<script type="text/javascript">
document.getElementById("bdshell_js").src = "http://bdimg.share.baidu.com/static/js/shell_v2.js?cdnversion=" + Math.ceil(new Date()/3600000)
</script>

<?php
}

/*
 * Insert analytics code snippets into head
 */
function dangopress_insert_analytics_snippets()
{
    /* Do not track administrator */
    if (current_user_can('manage_options'))
        return;

    $options = get_option('dangopress_options');

    if (!empty($options['google_webid'])) {
?>

<!-- Google Analytics -->
<script type="text/javascript">

var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo $options['google_webid']; ?>']);
_gaq.push(['_trackPageview']);

(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  　var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

</script>

<?php
    }

    if (!empty($options['bdtj_siteid'])) {
?>

<!-- Baidu Tongji -->
<script>
var _hmt = _hmt || [];
(function() {
    var hm = document.createElement("script");
    hm.src = "//hm.baidu.com/hm.js?<?php echo $options['bdtj_siteid']; ?>";
    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(hm, s);
})();
</script>

<?php
    }
}
add_action('wp_head', 'dangopress_insert_analytics_snippets');

/*
 * Show description box in category page
 */
function dangopress_category_description()
{
    $description = category_description();

    // Don't show the box if description is empty
    if (empty($description))
        return;

    $cat_name = single_cat_title('', false);
    $cat_ID = get_cat_ID($cat_name);

    // List the sub categories if have
    $sub_cats = wp_list_categories("child_of=$cat_ID&style=none&echo=0&show_option_none=");

    if (!empty($sub_cats)) {
        $sub_cats = str_replace("<br />", "", $sub_cats);  // strip the <br /> tag
        $sub_cats = str_replace("\n\t", ", ", $sub_cats);  // separated by comma

        $sub_cats = '<div class="sub-categories">包含子目录: ' . $sub_cats . '</div>';
    }
?>

    <div id="category-description" class="">
        <h2><?php echo $cat_name; ?> 类目</h2>
        <?php echo $description; ?>
        <?php echo $sub_cats; ?>
    </div>

<?php
}

/*
 * Add additional button in the wordpress editor
 */
function dangopress_add_quicktags()
{
    /* Check whether the quicktags.js is registered */
    if (!wp_script_is('quicktags'))
        return;
?>

    <script type="text/javascript">
    QTags.addButton('eg_h3', 'h3', '<h3>', '</h3>', '', '三级标题', 101);
    QTags.addButton('eg_h4', 'h4', '<h4>', '</h4>', '', '四级标题', 102);
    QTags.addButton('eg_pre', 'pre', '<pre>', '</pre>', '', '', 111);
    QTags.addButton('eg_prettify', 'prettify', '<pre class="prettyprint">', '</pre>', '', '代码高亮', 112);
    </script>

<?php
}
add_action('admin_print_footer_scripts', 'dangopress_add_quicktags');

?>
