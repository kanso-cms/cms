<?php

namespace Kanso\Http;

/**
 * Kanso HTTP Request
 *
 * This class provides a human-friendly interface of helper functions
 * to help validate/hande HTTP requests to the server
 *
 * This can be used for validating ajax requests, GET requests, 
 * POST requests or validating file requests.
 *
 * @property array \Kanso\Environment::extract()  $Environment
 * @property array \Kanso\Http\Header::extract()  $Headers
 */
class Request
{
    /**
     * Request method constants
     *
     * @var string 
     */
    const METHOD_HEAD     = 'HEAD';
    const METHOD_GET      = 'GET';
    const METHOD_POST     = 'POST';
    const METHOD_PUT      = 'PUT';
    const METHOD_PATCH    = 'PATCH';
    const METHOD_DELETE   = 'DELETE';
    const METHOD_OPTIONS  = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';

    /**
     * Accepted MIME Types
     *
     * @var array Associative array of files to MIME-types
     */
    protected $mimeMap = [

        // Image formats
        'jpg|jpeg|jpe'                 => 'image/jpeg',
        'gif'                          => 'image/gif',
        'png'                          => 'image/png',
        'bmp'                          => 'image/bmp',
        'tif|tiff'                     => 'image/tiff',
        'ico'                          => 'image/x-icon',

        // Video formats
        'asf|asx'                      => 'video/x-ms-asf',
        'wmv'                          => 'video/x-ms-wmv',
        'wmx'                          => 'video/x-ms-wmx',
        'wm'                           => 'video/x-ms-wm',
        'avi'                          => 'video/avi',
        'divx'                         => 'video/divx',
        'flv'                          => 'video/x-flv',
        'mov|qt'                       => 'video/quicktime',
        'mpeg|mpg|mpe'                 => 'video/mpeg',
        'mp4|m4v'                      => 'video/mp4',
        'ogv'                          => 'video/ogg',
        'webm'                         => 'video/webm',
        'mkv'                          => 'video/x-matroska',
        
        // Text formats
        'txt|asc|c|cc|h'               => 'text/plain',
        'csv'                          => 'text/csv',
        'tsv'                          => 'text/tab-separated-values',
        'ics'                          => 'text/calendar',
        'rtx'                          => 'text/richtext',
        'css'                          => 'text/css',
        'html|htm'                     => 'text/html',
        'xml'                          => 'text/xml', 
        
        // Audio formats
        'mp3|m4a|m4b'                  => 'audio/mpeg',
        'ra|ram'                       => 'audio/x-realaudio',
        'wav'                          => 'audio/wav',
        'ogg|oga'                      => 'audio/ogg',
        'mid|midi'                     => 'audio/midi',
        'wma'                          => 'audio/x-ms-wma',
        'wax'                          => 'audio/x-ms-wax',
        'mka'                          => 'audio/x-matroska',
        
        // Misc application formats
        'rtf'                          => 'application/rtf',
        'js'                           => 'application/javascript',
        'pdf'                          => 'application/pdf',
        'swf'                          => 'application/x-shockwave-flash',
        'class'                        => 'application/java',
        'tar'                          => 'application/x-tar',
        'zip'                          => 'application/zip',
        'gz|gzip'                      => 'application/x-gzip',
        'rar'                          => 'application/rar',
        '7z'                           => 'application/x-7z-compressed',
        'exe'                          => 'application/x-msdownload',
        
        // MS Office formats
        'doc'                          => 'application/msword',
        'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
        'wri'                          => 'application/vnd.ms-write',
        'xla|xls|xlt|xlw'              => 'application/vnd.ms-excel',
        'mdb'                          => 'application/vnd.ms-access',
        'mpp'                          => 'application/vnd.ms-project',
        'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
        'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
        'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
        'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
        'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
        'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
        'json'                         => 'application/json',
        
        // OpenOffice formats
        'odt'                          => 'application/vnd.oasis.opendocument.text',
        'odp'                          => 'application/vnd.oasis.opendocument.presentation',
        'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
        'o dg'                          => 'application/vnd.oasis.opendocument.graphics',
        'odc'                          => 'application/vnd.oasis.opendocument.chart',
        'odb'                          => 'application/vnd.oasis.opendocument.database',
        'odf'                          => 'application/vnd.oasis.opendocument.formula',
        
        // WordPerfect formats
        'wp|wpd'                       => 'application/wordperfect',
        
        // iWork formats
        'key'                          => 'application/vnd.apple.keynote',
        'numbers'                      => 'application/vnd.apple.numbers',
        'pages'                        => 'application/vnd.apple.pages',
    ];

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        
    }

    /**
     * Get HTTP method
     * @return string
     */
    public function getMethod()
    {
        return \Kanso\Kanso::getInstance()->Environment()['REQUEST_METHOD'];
    }

    /**
     * Is this a GET request?
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    /**
     * Is this a POST request?
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    /**
     * Is this a PUT request?
     * @return bool
     */
    public function isPut()
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Is this a PATCH request?
     * @return bool
     */
    public function isPatch()
    {
        return $this->getMethod() === self::METHOD_PATCH;
    }

    /**
     * Is this a DELETE request?
     * @return bool
     */
    public function isDelete()
    {
        return $this->getMethod() === self::METHOD_DELETE;
    }

    /**
     * Is this a HEAD request?
     * @return bool
     */
    public function isHead()
    {
        return $this->getMethod() === self::METHOD_HEAD;
    }

    /**
     * Is this an OPTIONS request?
     * @return bool
     */
    public function isOptions()
    {
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

    /**
     * Is this an Ajax request?
     * @return bool
     */
    public function isAjax()
    {
        $headers = \Kanso\Kanso::getInstance()->Headers();
        if (isset($headers['HTTP_X_REQUESTED_WITH']) && $headers['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ) return true;
        if (isset($headers['X_REQUESTED_WITH']) && $headers['X_REQUESTED_WITH'] === 'XMLHttpRequest' ) return true;
        return false;
    }

    /**
     * Is this a GET request for file?
     * @return bool
     */
    public function isFileGet()
    {
        if ($this->getContentType()) return true;
        return false;
    }

    /**
     * Fetch GET and POST request data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, false is returned,
     * unless there is a default value specified.
     *
     * @param  string            $key (optional)
     * @return array|mixed|false
     */
    public function fetch($key = null)
    {
        $env = \Kanso\Kanso::getInstance()->Environment();
        if (!$this->isGet()) {
            if ($key) {
                if (isset($_POST[$key])) return $_POST[$key];
                return false;
            }
            return array_merge($_POST, $_FILES);
        }
        else {
            $GETinfo  = array_merge(parse_url(rtrim($env['HTTP_HOST'].$env['REQUEST_URI'], '/')), pathinfo(rtrim($env['HTTP_HOST'].$env['REQUEST_URI'], '/')) );
            $GETinfo['page'] = 0;
            preg_match_all("/page\/(\d+)/", $env['REQUEST_URI'], $page);
            if (isset($page[1][0]) && !empty($page[1][0])) $GETinfo['page'] = (int) $page[1][0];
            if ($GETinfo['page'] === 1) $GETinfo['page'] = 0;
            if ($key) {
                if (isset($GETinfo[$key])) return $GETinfo[$key];
                return false;
            }
            return $GETinfo;
        }

        return false;
    }

    /**
     * Get MIME Type (type/subtype within Content Type header)
     *
     * @return string|false
     */
    public function getContentType()
    {
        if (!headers_sent()) {
            $pathinfo = $this->fetch();
            if (isset($pathinfo['path'])) {
                return $this->extToMime(\Kanso\Utility\Str::getAfterLastChar($pathinfo['path'], '.'));
            }
        }
        return false;
    }

    /**
     * Convert a file extension to a valid MIME-type
     *
     * @param  string           $ext File extension
     * @return string|false
     */
    public function extToMime($ext) 
    {
        $mimes = $this->mimeMap;
        foreach ($mimes as $type => $mime) {
            if (strpos($type, $ext) !== false) {
                return $mime;
            }
        }
        return false;
    }

    /**
     * Convert a to a MIME-type to a valid file extension
     *
     * @param  string           $mime mime type
     * @return string|false
     */
    public function mimeToExt($mime) 
    {
        $mimeMap = array_flip($this->mimeMap);
        if (isset($mimeMap[$mime])) return explode("|", $mimeMap[$mime])[0];
        return false;
    }

}
