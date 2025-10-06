<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Unverified User Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to keep users that have not verified their email or phone.
    | Set to 0 or a negative value to disable the scheduled purge entirely.
    */
    'unverified_user_retention_days' => env('UNVERIFIED_USER_RETENTION_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Purge Chunk Size
    |--------------------------------------------------------------------------
    |
    | How many records to process per chunk when deleting stale users. Tweaking
    | this can help balance memory usage and execution time when large batches
    | need to be removed.
    */
    'unverified_user_chunk_size' => env('UNVERIFIED_USER_PURGE_CHUNK', 100),
];
