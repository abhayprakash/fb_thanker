<html>
  <head></head>
  <body>

  <?php
	function curl($url) 
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	// App Credentials
	$app_id = '1437065273175468';
	$app_secret = '13683cd76860e09a29b5571e769d2c73';
	
	require_once('src/facebook.php');
	$config = array(
		'appId' => $app_id,
		'secret' => $app_secret,
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
	
	if($user_id) 
	{
		try 
		{
			$user_feed = $facebook->api($user_id.'?fields=feed.limit(1000).since(1386700231).fields(created_time,id,from,message)','GET');
			//var_dump($user_feed['feed']['data']);
			foreach($user_feed['feed']['data'] as $post)
			{
				$post_id = $post['id'];		
				
				$full_name = $post['from']['name'];
				$arr_name = explode(' ',$full_name);
				$friend_name = $arr_name[0];
				
				$facebook->api($post_id.'/likes','POST');
				
				$commentToMake = "Thanks " . $friend_name . "!! :)";
				$args = array(
					'message'   => $commentToMake
				);
				$ret_id = $facebook->api($post_id.'/comments','POST',$args);
				if($ret_id != 0)
				{
					echo "Success: Made Comment " . $commentToMake . '<br>';
				}
			}            
		} 	
		catch(FacebookApiException $e) 
		{
			$login_url = $facebook->getLoginUrl(); 
			echo 'Please <a href="' . $login_url . '">login.</a>';
			error_log($e->getType());
			error_log($e->getMessage());
		}   
	} 
	else 
	{
	  $login_url = $facebook->getLoginUrl();
	  echo 'Please <a href="' . $login_url . '">login.</a>';
	}

  ?>

  </body>
</html>