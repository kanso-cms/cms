<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

/**
 * CMS Query scripts methods.
 *
 * @author Joe J. Howard
 */
class Scripts extends Helper
{
    /**
     * Enqueue a JS script tag.
     *
     * @param string      $src      The script src URL
     * @param string|null $ver      The script version (optional) (default null)
     * @param bool        $inFooter Load the script in the footer (optional) (default false)
     */
    public function enqueue_script(string $src = '', string $ver = null, bool $inFooter = false): void
    {
        $ver = !$ver ? '' : '?v=' . $ver;

        if ($inFooter)
        {
            $this->parent->footerScripts[] = '<script type="text/javascript" src="' . $src . $ver . '"></script>';
        }
        else
        {
            $this->parent->headerScripts[] = '<script type="text/javascript" src="' . $src . $ver . '"></script>';
        }
    }

    /**
     * Enqueue a CSS stylesheet.
     *
     * @param string      $src   The stylesheet src URL
     * @param string|null $ver   The stylesheet version (default null)
     * @param string      $media The media for which this stylesheet has been defined
     */
    public function enqueue_style(string $src = '', string $ver = null, string $media = 'all'): void
    {
        $ver = !$ver ? '' : '?v=' . $ver;

        $this->parent->headerStyles[] = '<link rel="stylesheet" type="text/css" href="' . $src . $ver . '" media="' . $media . '">';
    }

    /**
     * Enqueue an inline style tag.
     *
     * @param string $css The CSS to enqueue
     */
    public function enqueue_inline_style(string $css = ''): void
    {
        $this->parent->headerStyles[] = '<style type="text/css">' . trim($css) . '</style>';
    }

    /**
     * Enqueue an inline style tag.
     *
     * @param string $js       The JS to enqueue
     * @param bool   $inFooter Load the script in the footer (optional) (default false)
     */
    public function enqueue_inline_script(string $js = '', bool $inFooter = false): void
    {
        if ($inFooter)
        {
            $this->parent->footerScripts[] = '<script type="text/javascript">' . trim($js) . '</script>';
        }
        else
        {
            $this->parent->headerScripts[] = '<script type="text/javascript">' . trim($js) . '</script>';
        }
    }

    /**
     * Get the kanso head.
     *
     * @return string
     */
    public function kanso_head(): string
    {
        $html = '';

        $html = implode("\n", $this->parent->headerStyles);

        $html .= implode("\n", $this->parent->headerScripts);

        return trim($html);
    }

    /**
     * Get the kanso head.
     *
     * @return string
     */
    public function kanso_footer(): string
    {
        return trim(implode("\n", $this->parent->footerScripts));
    }
}
