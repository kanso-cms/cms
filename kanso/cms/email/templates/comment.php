<p style="text-align: center;font-size: <?php echo $font_size_h3; ?>;font-weight: 300;color: <?php echo $body_color; ?>">New Comment</p>
<p>Hi <?php echo $name; ?>,</p>
<p>
    A new comment was made at <a href="<?php echo $websiteUrl; ?>" style="color: <?php echo $link_color; ?>"><?php echo $websiteName; ?></a>. 
    Read the comment at 
    <a style="color: <?php echo $link_color; ?>" href="<?php echo $the_pemalink; ?>#comment-<?php echo $comment_id; ?>">
       <?php echo $websiteName; ?>
    </a>
</p>

<!-- Clear Spacer : BEGIN -->
<tr>
    <td height="20" style="font-size: 0; line-height: 0;">
        &nbsp;
    </td>
</tr>
<!-- Clear Spacer : END -->

<!-- Thumbnail Right, Text Left : BEGIN -->
<tr>
    <td bgcolor="<?php echo $content_bg; ?>" dir="rtl" align="center" valign="top" width="100%">
        <table role="presentation" aria-hidden="true" align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <!-- Column : BEGIN -->
                <td width="33.33%" class="stack-column-center">
                    <table role="presentation" aria-hidden="true" align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td dir="ltr" valign="top">
                                <img src="<?php echo $the_thumbnail; ?>" aria-hidden="true" width="170" height="170" alt="<?php echo $websiteName; ?>" border="0" class="center-on-narrow hero-img" style="height: auto; display: block; width: 100%;">
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- Column : END -->
                <!-- Column : BEGIN -->
                <td width="66.66%" class="stack-column-center">
                    <table role="presentation" aria-hidden="true" align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td dir="ltr" valign="top" style="font-family: <?php echo $font_family; ?>; font-size: <?php echo $font_size; ?>; line-height: <?php echo $line_height; ?>; color: <?php echo $body_color; ?>; padding: 10px; text-align: left;" class="center-on-narrow">
                                <strong style="color:<?php echo $body_color; ?>;font-size: <?php echo $font_size_h6; ?>;"><?php echo $the_title; ?></strong>
                                <br><br>
                                    <?php echo $the_excerpt; ?>
                                <br><br>
                                <!-- Button : BEGIN -->
                                <?php echo \kanso\Kanso::instance()->Email->button($the_pemalink, 'Read', 'sm'); ?>
                                <!-- Button : END -->
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- Column : END -->
            </tr>
        </table>
    </td>
</tr>
<!-- Thumbnail Right, Text Left : END -->

<!-- Clear Spacer : BEGIN -->
<tr>
    <td height="80" style="font-size: 0; line-height: 0;">
        &nbsp;
    </td>
</tr>
<!-- Clear Spacer : END -->

<!-- FOOTER : BEGIN -->
<tr>
    <td>
        <p style="font-size: 11px;margin:0;color: <?php echo $color_gray; ?>; line-height: 19px; text-align: center;">
            You're receiving this message because you signed up to receive notifications from <a href="<?php echo $websiteUrl; ?>" style="color: <?php echo $link_color; ?>"><?php echo $websiteName; ?></a> on this thread.
        </p>
        <p style="font-size: 11px;margin:0;color: <?php echo $color_gray; ?>; line-height: 19px; text-align: center;">
            <a href="<?php echo $websiteUrl; ?>/comment-unsubscribe/?email=<?php echo md5($comment_email); ?>" style="color: <?php echo $color_gray_dark; ?>">Unsubscribe</a> 
        </p>
    </td>
</tr>
<!-- FOOTER : END  -->