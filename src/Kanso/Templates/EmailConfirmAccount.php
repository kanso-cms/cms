<?php

/**
 * Confirm account email for regular users
 *
 */
echo '
    <h2>You\'re nearly there</h2>
    <p>Hi '.$name.',</p>
    <p>Welcome to '.$websiteName.'. Before we can get started, we need to quickly verify your email address.</p>
    <p>Click the link below and sign in using your new username: '.$username.'.</p>
    <p>You can get started by following the link below:</p>
    <p><strong><a href="'.$confirmURL.'">Verify your email</a></strong></p>
    <p>Once your email is verified, <a href="'.$loginURL.'">sign in</a> to get started.</p>
    <table> 
    <tr>
      <td class="padding">
        <p>Or click here to get started:</p>
        <p><a style="text-decoration: none;color: #FFF;background-color: #62c4b6;border: solid #62c4b6;border-width: 5px 10px;line-height: 2;font-weight: bold;font-size: 12px;margin-right: 10px;text-align: center;cursor: pointer;display: inline-block;border-radius: 3px;" href="'.$confirmURL.'" class="btn-primary">Verify Your Email</a></p>
    </td>
    </tr>
    </table>
    <p>Thank you for choosing '.$websiteName.' </p>
';