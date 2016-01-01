<?php

/**
 * Kanso email for sending a reset-password link
 *
 */

echo '
 
    <p>Hi '.$data['name'].',</p>
    <p>Someone has requested that your password be reset on '.$data['website'].'.</p>
    <p>If this wasn\'t you, you can simply ignore this email and get on with things.</p>
    <p>If this was you however, you can reset your Kanso account password for '.$data['website'].' at the following url:</p>
    <p><a href="'.$data['website'].'/admin/reset-password?'.$data['key'].'" >'.$data['website'].'/admin/reset-password?'.$data['key'].'</a></p>
    <table>
        <tr>
            <td class="padding">
                <p>Or click here to reset your password:</p>
                <p><a style="text-decoration: none;color: #FFF;background-color: #62c4b6;border: solid #62c4b6;border-width: 5px 10px;line-height: 2;font-weight: bold;font-size: 12px;margin-right: 10px;text-align: center;cursor: pointer;display: inline-block;border-radius: 3px;" href="'.$data['website'].'/admin/reset-password?'.$data['key'].'" class="btn-primary">Reset your password</a></p>
            </td>
        </tr>
    </table>
    
';