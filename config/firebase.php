<?php

return [
    // path to service account JSON
    'credentials' => env('FIREBASE_CREDENTIALS')
        ? (str_starts_with(env('FIREBASE_CREDENTIALS'), DIRECTORY_SEPARATOR)
            ? env('FIREBASE_CREDENTIALS')
            : base_path(env('FIREBASE_CREDENTIALS')))
        : (env('FIREBASE_CREDENTIALS_PATH')
            ? (str_starts_with(env('FIREBASE_CREDENTIALS_PATH'), DIRECTORY_SEPARATOR)
                ? env('FIREBASE_CREDENTIALS_PATH')
                : base_path(env('FIREBASE_CREDENTIALS_PATH')))
            : null),

    // project id for Firestore REST calls
    'project_id' => env('FIREBASE_PROJECT_ID'),
];