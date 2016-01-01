<?php

/**
 * Kanso email content for notifying of a new comment reply
 *
 */

echo '

    <p style="font-size: 17px;margin:0;color: #5a6166;font-weight: 300;line-height: 31px;">
        Hi '.$data['name'].',<br>
        Someone has replied to a comment you made at
        <a style="text-decoration: none;outline: none;color: #62c4b6;" href="'.$data['websiteLink'].'">
            <strong>'.$data['website'].'</strong>
        </a> 
        on:
    </p>
    <h3 style="font-size: 23px;margin: 4px 0;">
        <a style="text-decoration: underline;outline: none;color: #62c4b6;font-weight: normal;" href="'.$data['articlePermalink'].'">'.$data['articleTitle'].'</a>
    </h3>
    <span style="display: block;width: 100%;height: 26px;border-bottom: 2px solid #e0e6e9;"></span>

    <table style="border-collapse: collapse;width: 100%;font-family: &quot;Helvetica Neue&quot;, Helvetica, &quot;Segoe UI&quot;, Arial, freesans, sans-serif;color: #1e2629;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-size: 14px;margin: 20px 0;">
    	<thead>
    		<tr></tr>
    	</thead>
    	<tbody style="margin: 20px 0;">
    		<tr>
    			<td style="border-collapse: collapse;width: 100%;float: left;display: block;position: relative;padding-left: 30px;font-size: 14px;line-height: 1px;">
                	<img alt="" src="'.$data['parent']['avatar'].'" width="20" height="20" style="border: 0 none;height: 20px;line-height: 100%;outline: none;text-decoration: none;position: absolute;top: 5px;left: 0;width: 20px;display:inline-block;float:left;" />
                	<p style="font-weight: 500;display: inline-block;float: left;margin: 0;line-height: 21px;padding-left: 13px;">
                		'.$data['parent']['name'].'
                	</p>
                </td>
                <td style="border-collapse: collapse;width: 100%;float: left;display: block;margin: 15px 0;padding-left: 30px;">
                	<p style="margin: 10px 0;width: 100%;display: block;margin-top: 0 !important;margin-bottom: 0 !important;">
                		'.$data['parent']['content'].'
                	</p>
                </td>
                <td style="border-collapse: collapse;width: 100%;float: left;display: block;font-size: 12px;padding-left: 30px;">
                	<span datetime="2015-07-15T07:24:54+02:00" style="color: #A6A6A6;">
                		'.date( 'M d, Y', $data['parent']['date']).'
                	</span> • 
                	<a href="'.$data['articlePermalink'].'#'.$data['parent']['id'].'" style="text-decoration: none;outline: none;color: #62c4b6;">
                		#
                	</a> • 
                	<a href="'.$data['articlePermalink'].'#'.$data['parent']['id'].'" style="text-decoration: none;outline: none;color: #62c4b6;">
                		Reply
                	</a>
                </td>
                <td style="border-collapse: collapse;width: 100%;float: left;display: inline-table !important;padding-left: 20px;margin-top: 20px;border-left: 5px solid #5182e5;background-color: rgba(120, 177, 254, 0.09);">
                    <table style="border-collapse: collapse;width: 100%;font-family: &quot;Helvetica Neue&quot;, Helvetica, &quot;Segoe UI&quot;, Arial, freesans, sans-serif;color: #1e2629;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-size: 14px;margin: 20px 0;">
                    	<thead>
                    		<tr></tr>
                    	</thead>
                    	<tbody style="margin: 20px 0;"><tr><td style="border-collapse: collapse;width: 100%;float: left;display: block;position: relative;padding-left: 30px;font-size: 14px;line-height: 1px;">
                    		<tr>
    							<td style="border-collapse: collapse;width: 100%;float: left;display: block;position: relative;padding-left: 30px;font-size: 14px;line-height: 1px;">
                    				<img alt="" src="'.$data['avatar'].'" width="20" height="20" style="border: 0 none;height: 20px;line-height: 100%;outline: none;text-decoration: none;position: absolute;top: 5px;left: 0;width: 20px;display:inline-block;float:left;" />
                    				<p style="font-weight: 500;display: inline-block;float: left;margin: 0;line-height: 21px;padding-left: 13px;">
                    					'.$data['name'].'
                    				</p>
                				</td>
                            	<td style="border-collapse: collapse;width: 100%;float: left;display: block;margin: 15px 0;padding-left: 30px;">
                            		<p style="margin: 10px 0;width: 100%;display: block;margin-top: 0 !important;margin-bottom: 0 !important;">
                            			'.$data['content'].'
                            		</p>
                            	</td>
                            	<td style="border-collapse: collapse;width: 100%;float: left;display: block;font-size: 12px;padding-left: 30px;">
                            		<span datetime="2015-07-15T07:24:54+02:00" style="color: #A6A6A6;">
                            			'.date( 'M d, Y', $data['date']).'
                            		</span> •
                            		<a href="'.$data['articlePermalink'].'#'.$data['id'].'" style="text-decoration: none;outline: none;color: #62c4b6;">
                            			#
                            		</a> •
                            		<a href="'.$data['articlePermalink'].'#'.$data['id'].'" style="text-decoration: none;outline: none;color: #62c4b6;">
                            			Reply
                            		</a>
                            	</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <span style="display: block;width: 100%;height: 26px;border-top: 1px solid #e0e6e9;"></span>
    <p style="font-size: 13px;margin:0;color: #939ca2;font-weight: 300;line-height: 19px;">
        You\'re receiving this message because you\'re signed up to receive notifications from '.$data['website'].' on this thread.<br>
        You can unsubscribe from all emails about activity on this thread <a href="" style=""text-decoration: none;outline: none;">here</a> or unsubscribe to email notifications on replies to this comment <a href="" style="text-decoration: none;outline: none;">here</a>.
    </p>
    
';