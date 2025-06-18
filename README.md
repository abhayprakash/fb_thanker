# Facebook PHP SDK

This project provides a PHP SDK for interacting with the Facebook API. It allows you to easily integrate Facebook authentication and API functionality into your PHP applications.

## Requirements

*   PHP 5.x or higher (based on the code syntax)
*   PHP CURL extension
*   PHP JSON extension
*   A Facebook Application ID and Secret (obtainable from the Facebook Developer portal)

## Installation

1.  Download the SDK files.
2.  Include the `src/facebook.php` file in your PHP script:

    ```php
    require_once 'path/to/your/src/facebook.php';
    ```

    Replace `path/to/your/` with the actual path to the `src` directory.

## Basic Usage

Here's a simple example of how to use the SDK:

```php
<?php

require_once 'src/facebook.php'; // Adjust path as needed

// Configuration
$config = array(
    'appId'  => 'YOUR_APP_ID',
    'secret' => 'YOUR_APP_SECRET',
    'fileUpload' => true, // Optional: enable for photo uploads
);

// Instantiate the Facebook class
$facebook = new Facebook($config);

// Get the current user's ID
$userId = $facebook->getUser();

if ($userId) {
    try {
        // User is logged in and authenticated
        // You can now make API calls
        $userProfile = $facebook->api('/me');
        echo "Hello, " . $userProfile['name'];

    } catch (FacebookApiException $e) {
        // Handle API errors
        error_log($e->getType() . ': ' . $e->getMessage());
        $userId = null; // User might not be properly authenticated
    }
}

if (!$userId) {
    // User is not logged in or not authenticated
    // Generate a login URL
    $loginUrlParams = array(
        'scope' => 'email,user_birthday', // Request desired permissions
        'redirect_uri' => 'http://your-website.com/your-callback-url.php' // Your callback URL
    );
    $loginUrl = $facebook->getLoginUrl($loginUrlParams);
    echo '<a href="' . $loginUrl . '">Login with Facebook</a>';
}

?>
```

**Important:**

*   Replace `'YOUR_APP_ID'` and `'YOUR_APP_SECRET'` with your actual Facebook App ID and Secret.
*   Replace `'http://your-website.com/your-callback-url.php'` with the URL you want Facebook to redirect to after login. This URL should also handle the Facebook login flow.
*   The `redirect_uri` provided to `getLoginUrl` must be an absolute URL and point to a script on your server that can handle the Facebook callback (e.g., by calling `getUser()` again and proceeding with your application logic).
*   Ensure `session_start()` is called at the beginning of your script if it's not already, as this SDK relies on PHP sessions. The `Facebook` class constructor attempts to start a session if one isn't active, but it's good practice to manage it explicitly.

## Common API Calls

The SDK provides methods for common Facebook interactions:

### Get Login URL

To get a URL to redirect users to for Facebook login:

```php
$params = array(
  'scope' => 'email,user_likes', // Specify required permissions
  'redirect_uri' => 'https://your-app.com/callback.php' // Your callback URL
);
$loginUrl = $facebook->getLoginUrl($params);
// Redirect the user to $loginUrl or display it as a link
```

### Get Logout URL

To get a URL to log a user out of your app and Facebook:

```php
$params = array(
  'next' => 'https://your-app.com/logged_out.php' // URL to redirect to after logout
);
$logoutUrl = $facebook->getLogoutUrl($params);
// Redirect the user to $logoutUrl or display it as a link
```
Note: The user must have an active session for `getLogoutUrl()` to include their access token, which is necessary for a full logout from Facebook.

### Making API Calls

You can make calls to the Facebook Graph API using the `api()` method:

```php
try {
  // Get the user's profile
  $userProfile = $facebook->api('/me');

  // Get the user's friends
  $friends = $facebook->api('/me/friends');

  // Post a message to the user's wall (requires 'publish_stream' or 'publish_actions' permission)
  // Note: publish_stream is deprecated. For modern apps, use the Graph API's Post edge.
  // This SDK version might use older practices.
  /*
  $statusUpdate = $facebook->api('/me/feed', 'POST', array(
    'message' => 'Hello from my PHP app!',
    'link'    => 'https://your-app.com'
  ));
  */

  // Uploading a photo (requires 'publish_stream' or 'publish_actions' and 'user_photos' permissions)
  // Ensure 'fileUpload' => true was set in the Facebook constructor config
  /*
  $photoDetails = array(
    'message' => 'My new photo!',
    'source'  => '@' . realpath('path/to/your/image.jpg') // Use '@' for file paths
  );
  $uploadPhoto = $facebook->api('/me/photos', 'POST', $photoDetails);
  */

} catch (FacebookApiException $e) {
  // Handle API errors
  error_log("Error type: " . $e->getType());
  error_log("Error message: " . $e->getMessage());
}
```
The first argument to `api()` is the Graph API path (e.g., `/me`, `/PAGE_ID/feed`).
The second argument is the HTTP method (e.g., `GET`, `POST`, `DELETE`). Defaults to `GET`.
The third argument is an array of parameters for the API call.

### Get User ID

To get the ID of the currently logged-in user:

```php
$userId = $facebook->getUser();
if ($userId) {
  // User is logged in
  echo "User ID: " . $userId;
} else {
  // User is not logged in
  echo "User not logged in.";
}
```
This method returns the user's Facebook ID if they are authenticated with your app, otherwise it returns `0` or `null`.

## Error Handling

When an API call fails, the SDK throws a `FacebookApiException`. You should wrap your API calls in a `try...catch` block to handle these exceptions:

```php
try {
    $userProfile = $facebook->api('/me');
} catch (FacebookApiException $e) {
    echo "Error type: " . $e->getType() . "\n";
    echo "Error message: " . $e->getMessage() . "\n";
    // $e->getResult() will return the full error data from Facebook
    // Log the error, display a user-friendly message, etc.
}
```

## License

This SDK is licensed under the Apache License, Version 2.0. You can find the full license text in the source files or at:

[http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0)
