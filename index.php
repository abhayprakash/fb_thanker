<html>
  <head></head>
  <body>

  <?php
  
  function curl($url) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
      curl_setopt($ch, CURLOPT_URL, $url);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
      }
  require_once('src/facebook.php');
  $app_id = '1437065273175468';
  $config = array(
	'appId' => '1437065273175468',
	'secret' => '13683cd76860e09a29b5571e769d2c73',
	'allowSignedRequest' => false // optional but should be set to false for non-canvas apps
  );

  $facebook = new Facebook($config);
  $user_id = $facebook->getUser();
  
    /*=============================Authorizing============================*/
	$list = 'read_stream,friends_birthday,publish_stream';
	$direct_here = 'https://www.facebook.com/dialog/oauth?client_id='.$app_id.'&redirect_uri=http://localhost/fb_reply_bd_wish_post/&scope='.$list.'&response_type=token';
	echo 'Please <a href="' . $direct_here . '">authorise.</a>'.'<br>';
	/*====================================================================*/
	
	$access_token = $facebook->getAccessToken();
	$facebook->setAccessToken($access_token);
    
	if($user_id) {
	  // We have a user ID, so probably a logged in user.
      // If not, we'll get an exception, which we handle below.
      try {

			$user_feed = $facebook->api('100000033203253?fields=feed.limit(1000).since(1386700231).fields(created_time,id,from)','GET');
			//var_dump($user_feed['feed']['data'][0]);
			foreach($user_feed['feed']['data'] as $post)
			{
				//echo "post id " . $post['id'] . '<br>';
				//echo "actor id " . $post['from']['id'];
				
				$actor_id = $post['from']['id'];
				$full_name = $post['from']['name'];
				//$ask_name = $facebook->api('/'.$actor_id,'GET');
				$arr_name = explode(' ',$full_name);
				$friend_name = $arr_name[0];
				
				$commentToMake = "Thanks " . $friend_name . "!! :)";
				echo $commentToMake . '<br>';
			}			
		} catch(FacebookApiException $e) {
        // If the user is logged out, you can have a 
        // user ID even though the access token is invalid.
        // In this case, we'll get an exception, so we'll
        // just ask the user to login again here.
        $login_url = $facebook->getLoginUrl(); 
        echo 'Please <a href="' . $login_url . '">login.</a>';
        error_log($e->getType());
        error_log($e->getMessage());
      }   
    } else {

      // No user, print a link for the user to login
      $login_url = $facebook->getLoginUrl();
      echo 'Please <a href="' . $login_url . '">login.</a>';

    }

  ?>

  </body>
</html>