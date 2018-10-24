<p style="text-align: center;font-size: <?php echo $font_size_h3; ?>;font-weight: 300;color: <?php echo $body_color; ?>;">Reset Your Password</p>
<p>Hi <?php echo $name; ?>,</p>
<p>Someone has requested that your password be reset on <a href="<?php echo $websiteUrl; ?>" style="color: <?php echo $link_color; ?>"><?php echo $websiteName; ?></a></p>
<p>If this wasn't you, please ensure your email is secure as someone may be trying to compromise your account.</p>
<p>If this was you however, you can reset your Kanso account password by clicking the link below:</p>
<p><a href="<?php echo $resetUrl; ?>" style="color: <?php echo $link_color; ?>"><?php echo $resetUrl; ?></a></p>
<table>
    <tr>
        <td style="padding: 20px;">
            <p><?php echo \kanso\Kanso::instance()->Email->button($resetUrl, 'Reset Your Password'); ?></p>
        </td>
    </tr>
</table>