<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Credentials
    |--------------------------------------------------------------------------
    |
    | Product and inventory images are stored on Cloudinary rather than on the
    | container's local disk. A Render container's filesystem is ephemeral, so
    | anything written locally is lost on every deploy and on every wake from
    | the free tier's sleep.
    |
    | When these are unset the uploader falls back to the local public disk, so
    | local development needs no Cloudinary account.
    |
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Upload Folder
    |--------------------------------------------------------------------------
    |
    | Everything is uploaded under a single folder so the assets can be found
    | and managed together in the Cloudinary Media Library.
    |
    */

    'folder' => env('CLOUDINARY_FOLDER', 'canson'),

    /*
    |--------------------------------------------------------------------------
    | Delivery Format
    |--------------------------------------------------------------------------
    |
    | Products render as small thumbnails in list views but are also opened
    | full size, so request an auto format (webp/avif where supported) with a
    | capped width rather than uploading every image at full resolution.
    |
    */

    'width' => (int) env('CLOUDINARY_WIDTH', 1600),

    /*
    |--------------------------------------------------------------------------
    | Signed Uploads
    |--------------------------------------------------------------------------
    |
    | Leave disabled unless uploads need to be authorised server-side. It is
    | only used to verify requests the server itself sends, so it does not
    | restrict what a browser can upload.
    |
    */

    'use_signed_uploads' => (bool) env('CLOUDINARY_USE_SIGNED_UPLOADS', false),

];
