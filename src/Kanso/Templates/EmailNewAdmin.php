<?php
/**
 * Kanso email for newly registered admins
 *
 */

echo '

    <p>Hi there,</p>
    <p>You have been invited to become an author on '.$websiteName.'.</p>
    <p>To get started, you can login to the Admin Panel at :</p>
    <p><a href="'.$loginURL.'">'.$loginURL.'</a></p>
    <p>Using the following credenitals:</p>
    <p><strong>Username: </strong> '.$username.'</p>
    <p><strong>Password: </strong> '.$password.'</p>
    <table>
    <tr>
      <td class="padding">
        <p>Or click here to get started:</p>
        <p><a style="text-decoration: none;color: #FFF;background-color: #62c4b6;border: solid #62c4b6;border-width: 5px 10px;line-height: 2;font-weight: bold;font-size: 12px;margin-right: 10px;text-align: center;cursor: pointer;display: inline-block;border-radius: 3px;" href="'.$loginURL.'" class="btn-primary">Login</a></p>
    </td>
    </tr>
    </table>
    
';