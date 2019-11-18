<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

/**
 * Filter search request.
 *
 * @author Joe J. Howard
 */
class Attachment extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $blogPrefix      = $this->container->Config->get('cms.blog_location');
        $urlParts        = array_filter(explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI)));

        $attachmentName  = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $attachmentSlug  = Str::getBeforeLastChar($attachmentName, '.');
        $attachemmentExt = Str::getAfterLastChar($attachmentName, '.');
        $uploadsUrl      = str_replace($this->container->Request->environment()->DOCUMENT_ROOT, $this->container->Request->environment()->HTTP_HOST, $this->container->Config->get('cms.uploads.path'));
        $isImage         = in_array($attachemmentExt, ['jpg', 'jpeg', 'png', 'gif']);
        $thumbnailSizes  = array_keys($this->container->Config->get('cms.uploads.thumbnail_sizes'));
        $attachmentURL   = $uploadsUrl . '/' . $attachmentSlug . '.' . $attachemmentExt;
        $attachment      = $this->container->MediaManager->provider()->byKey('url', $attachmentURL, true);
        $attachmentSize  = 'original';

        // 404 If the attachment does not exist
        if (!$attachment)
        {
            $this->container->Response->status()->set(404);

            return false;
        }

        $postRow =
        [
            'created'      => $attachment->date,
            'modified'     => $attachment->date,
            'status'       => 'published',
            'type'         => 'attachment',
            'author_id'    => $attachment->uploader_id,
            'title'        => $attachment->title,
            'excerpt'      => $attachment->alt,
            'thumbnail_id' => $attachment->id,
            'comments_enabled' => -1,
        ];

        $this->parent->attachmentSize = $attachmentSize;
        $this->parent->attachmentURL  = $attachmentURL;

        $this->parent->queryStr    = '';
        $this->parent->posts       = [$this->container->PostManager->provider()->newPost($postRow)];
        $this->parent->postCount   = count($this->parent->posts);
        $this->parent->requestType = 'attachment';

        return true;
    }
}
