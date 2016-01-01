<?php

/**
 * Kanso email for sending a reset-password link
 *
 */

echo '
 
    <p>Hi '.$data['name'].',</p>
    <p>This is just a quick reminder that you have successfully registered as an author at '.$data['website'].'.</p>
    <p>You can access the admin panel at the following url:</p>
    <p><a href="'.$data['website'].'/admin/login" >'.$data['website'].'/admin/login</a></p>
    <p>With your username: <strong>'.$data['username'].'</strong></p>
    <table>
        <tr>
            <td class="padding">
                <p>Or click here to login:</p>
                <p><a style="text-decoration: none;color: #FFF;background-color: #62c4b6;border: solid #62c4b6;border-width: 5px 10px;line-height: 2;font-weight: bold;font-size: 12px;margin-right: 10px;text-align: center;cursor: pointer;display: inline-block;border-radius: 3px;" href="'.$data['website'].'/admin/login/" class="btn-primary">Login</a></p>
            </td>
        </tr>
    </table>
    
';