<?php

/**
 * Kanso email for sending a confirmation that a password was reset
 *
 */

echo '
 
    <p>Hi '.$data['name'].',</p>
    <p>Someone has reset your password on '.$data['website'].'.</p>
    <p>If this wasn\'t you, you should probably reset your password to something more secure, and change your email password also.</p>
    <p>You can reset your password again at the link below:</p>
    <p><a href="'.$data['website'].'/admin/forgot-password" >'.$data['website'].'/admin/forgot-password</a></p>
    <table>
        <tr>
            <td class="padding">
                <p><a style="text-decoration: none;color: #FFF;background-color: #62c4b6;border: solid #62c4b6;border-width: 5px 10px;line-height: 2;font-weight: bold;font-size: 12px;margin-right: 10px;text-align: center;cursor: pointer;display: inline-block;border-radius: 3px;" href="'.$data['website'].'/admin/reset-password/" class="btn-primary">Reset your password</a></p>
            </td>
        </tr>
    </table>
    
';