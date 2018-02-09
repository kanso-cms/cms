<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\email;

use kanso\framework\file\Filesystem;

/**
 * CMS email utility
 *
 * @author Joe J. Howard
 */
class Email
{
    /**
     * Theme for email styling
     *
     * @var array
     */
    private $theme = 
    [
        'body_bg'         => '#FFFFFF',
        'content_bg'      => '#FFFFFF',
        'content_border'  => '1px solid #DADADA',
        'font_family'     => '"Helvetica Neue", Helvetica, Arial, sans-serif',
        'font_size'       => '13.5px',
        'line_height'     => '27px',
        'body_color'      => '#4e5358',
        'link_color'      => '#62c4b6',
        'color_gray'      => '#c7c7c7',
        'color_gray_dark' => '#b1b1b1',
        'btn_bg'          => '#62c4b6',
        'btn_color'       => '#ffffff',
        'btn_hover_bg'    => '#48ad9e',
        'btn_size'        => '18px',
        'btn_font_size'   => '13px',
        'border_radius'   => '3px',
        'logo_url'        => '',
        'logo_link'       => 'http://kanso-cms.github.io/',
        'font_size_h1'    => '30px',
        'font_size_h2'    => '28px',
        'font_size_h3'    => '24px',
        'font_size_h4'    => '20px',
        'font_size_h5'    => '18px',
        'font_size_h6'    => '15px',
    ];

    /**
     * Filesystem instance
     *
     * @var \kanso\framework\file\Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\file\Filesystem $filesystem Filesystem instance
     * @param  array.                           $theme      Array of theme options (optional) (default [])
     */
    public function __construct(Filesystem $filesystem, $theme = [])
    {
        $this->filesystem = $filesystem;

        $this->theme = array_merge($this->theme, $theme);
    }

    /**
     * Load a present template
     *
     * @access public
     * @param  string $template Template name 
     * @param  array  $vars     Vars to send to the template
     * @return string
     */
    public function preset(string $template, array $vars = []): string
    {
        $variables = array_merge($this->theme, $vars);
        
        $filePath  = dirname(__FILE__).DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.'.php';
        
        return $this->filesystem->ob_read($filePath, $variables);
    }

    /**
     * Send an HTML or plain text email
     *
     * @access public
     * @param  string $emailTo      The email address to send the email to
     * @param  string $senderName   The name of the sender
     * @param  string $senderEmail  The email address of the sender
     * @param  string $subject      The subject of the email
     * @param  string $content      The message to be sent
     * @param  string $format       html or plain
     * @return bool
     */
    public function send(string $toEmail, string $senderName, string $senderEmail, string $subject, string $content, string $format = 'html'): bool
    {
        if ($format === 'html')
        {
            $headers   = 'MIME-Version: 1.0' . "\r\n";
            $headers  .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers  .= 'From: '.$senderEmail.' <'.$senderName.'>' . "\r\n";
        }
        else
        {
            $headers = 'From: '.$senderEmail.' <'.$senderName.'>' . "\r\n";
        }
        
        return mail($toEmail, $subject, $content, $headers);
    }

    /**
     * Load the Kanso HTML email template with a custom msg
     *
     * @access public
     * @param  string $subject The subject of the email
     * @param  string $content The message to be sent
     * @return string
    */
    public function html(string $subject, string $content): string
    {
        $body_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'body.php';

        $_vars = 
        [
            'subject' => $subject, 
            'content' => $content,
            'logoSrc' => $this->theme['logo_url'],
        ];

        $variables = array_merge($this->theme, $_vars);
        
        return $this->filesystem->ob_read($body_path, $variables);
    }

    /**
     * Set or get the theme variables
     *
     * @access public
     * @param  array $theme Assoc array of theme variables (optional) (default [])
     * @return array
     */
    public function theme(array $theme = []): array
    {
        $this->theme = array_merge($this->theme, $theme);
        
        return $this->theme;
    }

    /**
     * Get a button
     *
     * @access  public
     * @param   string $href    URL for the link
     * @param   string $text    Text inside the button
     * @param   string $size    'xs'|'sm'|'md'|'lg'|'xl'
     * @return  string
     */
    public function button(string $href, string $text, string $size = 'md'): string
    {
        $padding   = $this->theme['btn_size'];
        $gutter    = '&nbsp;&nbsp;&nbsp;&nbsp';
        $font_size = $this->theme['btn_font_size'];
        
        if ($size === 'xs')
        {
            $padding   = '10px';
            $gutter    = '&nbsp;';
            $font_size = '11px';
        }
        else if ($size === 'sm')
        {
            $padding   = '13px';
            $gutter    = '&nbsp;&nbsp;';
            $font_size = '12px';
        }
        else if ($size === 'md')
        {
            $padding   = $this->theme['btn_size'];
            $gutter    = '&nbsp;&nbsp;&nbsp;&nbsp;';
            $font_size = $this->theme['btn_font_size'];
        }
        else if ($size === 'lg')
        {
            $padding = '20px';
            $gutter  = '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        else if ($size === 'xl')
        {
            $padding = '24px';
            $gutter  = '&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        return '
        <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
            <tbody><tr>
                <td style="border-radius: '.$this->theme['border_radius'].'; background: '.$this->theme['btn_bg'].'; text-align: center;" class="button-td">
                    <a href="'.$href.'" style="background: '.$this->theme['btn_bg'].'; border: '.$padding.' solid '.$this->theme['btn_bg'].'; font-family: sans-serif; font-size: '.$font_size.'; line-height: 1.1; text-align: center; text-decoration: none; display: block; border-radius: '.$this->theme['border_radius'].';" class="button-a">
                        <span style="color:'.$this->theme['btn_color'].';" class="button-link">'.$gutter.strtoupper($text).$gutter.'</span>
                    </a>
                </td>
            </tr></tbody>
        </table>
        ';
    }
}
