<?php

namespace App\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    /*
     * Get the base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $storeId = 0;
        $model = $media->model;

        if ($model instanceof \App\Models\Store) {
            $storeId = $model->id;
        } elseif (isset($model->store_id)) {
            $storeId = $model->store_id;
        }

        $folder = 'media';
        if ($media->collection_name === 'settings') {
            $folder = 'settings';
        } elseif ($media->collection_name === 'attachments') {
            $folder = 'attach';
        }

        // Structure: store_{id}/{folder}/{media_id}
        return "store_{$storeId}/{$folder}/{$media->id}";
    }
}
