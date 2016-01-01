<?php

/**
 * Kanso email for sending a username reminder
 *
 */

echo '
 
    <p>Hi '.$data['name'].',</p>
    <p>Someone has requested a user-name reminder on '.$data['website'].'.</p>
    <p>If this wasn\'t you, you can simply ignore this email and get on with things.</p>
    <p>If this was you however, your username is as follows:</p>
    <p><strong>'.$data['username'].'</strong></p>
    <table>
        <tr>
            <td class="padding">
                <p><a style="text-decoration: none;color: #FFF;background-color: #62c4b6;border: solid #62c4b6;border-width: 5px 10px;line-height: 2;font-weight: bold;font-size: 12px;margin-right: 10px;text-align: center;cursor: pointer;display: inline-block;border-radius: 3px;" href="'.$data['website'].'/admin/login/" class="btn-primary">Login</a></p>
            </td>
        </tr>
    </table>
    
';