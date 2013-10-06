<?php
/**
 * The Template for displaying all single posts.
 *
 * @package dangopress
 */
 
get_header();?>

<?php if ( have_posts( )) : while ( have_posts() ) : the_post(); ?>
<div <?php post_class(); ?>>
    <div class="post-header">
        <h1 class="post-title"><?php the_title(); ?></h1>
    </div>
    <div class="post-meta clearfix">
        <span class="post-time"><i class="icon-calendar"></i><?php the_time('Y/m/d'); ?></span>
        <span class="post-author"><i class="icon-user"></i>by <?php the_author_link(); ?></span>
        <?php if (function_exists('the_user_views')): ?>
            <span class="post-view"><i class="icon-sun"></i><?php the_user_views(); ?></span>
        <?php endif; ?>
        <span class="post-comment">
            <i class="icon-comments"></i><?php comments_popup_link('暂无评论', '1 个评论', '% 个评论', 'comments-link' ); ?>
       </span>
    </div>

    <div class="post-content clearfix">
        <?php the_content(); ?>
    </div>
</div>

<?php if (function_exists('related_posts')): ?>
<div class="clearfix related-posts">
    <div class="caption">
        <i class="icon-plus-sign-alt"></i> 与<h2><?php the_tags(' ', ', ', ' '); ?></h2>相关的文章
    </div>
    <?php related_posts(); ?>
</div>
<?php endif; ?>

<div class="post-footer clearfix">
<div id="post-pagination" class="alignleft">
    <div class="post-prev">
        <span class="icon-chevron-sign-left"></span> 上一篇:
        <?php
            $prev_post = get_previous_post();

            if (!empty($prev_post)) { ?>
                <a rel="prev" href="<?php echo get_permalink($prev_post->ID); ?>"><?php echo $prev_post->post_title; ?></a>
        <?php
            } else {
                echo '<a href="javascript:void(0);">已经是最新一篇文章</a>';
            }
        ?>
    </div>
    <div class="post-next">
        <span class="icon-chevron-sign-right"></span> 下一篇:
        <?php
            $next_post = get_next_post();

            if (!empty($next_post)) { ?>
                <a rel="prev" href="<?php echo get_permalink($next_post->ID); ?>"><?php echo $next_post->post_title; ?></a>
        <?php
            } else {
                echo '<a href="javascript:void(0);">已经是最后一篇文章</a>';
            }
        ?>
    </div>
</div>
<div id="social-share" class="alignright">  
    <?php dangopress_place_bdshare(); ?>
</div>
</div>

<?php endwhile; endif; ?>
<?php comments_template(); ?>
                        
<?php get_footer(); ?>