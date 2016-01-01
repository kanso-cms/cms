<?php

/**
 * Kanso email for sending a reset-password link
 *
 */

echo '
 
    <p>Hi there,</p>
    <p>'.$data['name'].' has sent you an invitation to become an author on '.$data['website'].'.</p>
    <p>To get started, you\'ll need to register on '.$data['website'].' as an author.</p>
    <p>You can get started by following the link below:</p>
    <p><strong><a href="'.$data['link'].'">'.$data['link'].'</a></strong></p>
    <table>
    <tr>
      <td class="padding">
        <p>Or click here to get started:</p>
        <p><a style="text-decoration: none;color: #FFF;background-color: #62c4b6;border: solid #62c4b6;border-width: 5px 10px;line-height: 2;font-weight: bold;font-size: 12px;margin-right: 10px;text-align: center;cursor: pointer;display: inline-block;border-radius: 3px;" href="'.$data['link'].'" class="btn-primary">Register</a></p>
    </td>
    </tr>
    </table>
    
';