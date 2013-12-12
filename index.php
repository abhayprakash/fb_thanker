<html>
  <head></head>
  <body>

  <?php
	ini_set('max_execution_time', 600);
	
	// Put Values -----------------------------------
	$app_id = '1437065273175468';
	$app_secret = 'PUT HERE YOUR APP SECRET';
	$birthDate = '2013-12-11 00:00:00';
	$doLikes = true;
	$doComments = true;
	//-----------------------------------------------
	
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
	
	$birthDateTime = strtotime($birthDate);
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
			$user_feed = $facebook->api($user_id.'?fields=feed.limit(1000).since('.$birthDateTime.').fields(created_time,id,from,likes)','GET');
			//var_dump($user_feed['feed']['data']);
			foreach($user_feed['feed']['data'] as $post)
			{
				$post_id = $post['id'];                
				$full_name = $post['from']['name'];
				
				if(isset($post['likes']))
				{
					$continueToNext = false;
					foreach($post['likes']['data'] as $post_like)
					{
						if($post_like['id'] == $user_id)
						{
							echo "Continuing: I have already liked it<br>";
							$continueToNext = true;
							break;
						}
					}
					
					if($continueToNext)
					{
						continue;
					}
				}
				
				if($doLikes)
				{
					try
					{
						$facebook->api($post_id.'/likes','POST');
						echo "liked for " . $full_name . '<br>';
					}
					catch(FacebookApiException $e)
					{
						$Result = $e->getResult();
						if($Result['error']['code'] == 17 || $Result['error']['code'] == 4)
						{
							echo "api call rate limit reached<br>";
							sleep(2);
							$facebook->api($post_id.'/likes','POST');
							echo "liked for " . $full_name . '<br>';
						}
					}
				}
				
				if($doComments)
				{
					$arr_name = explode(' ',$full_name);
					$friend_name = $arr_name[0];
				
					$commentToMake = "Thanks " . $friend_name . "!! :)";
					$args = array(
							'message'   => $commentToMake
					);
					
					try
					{
						$ret_id = $facebook->api($post_id.'/comments','POST',$args);
						if($ret_id != 0)
						{
							echo "Success: Made Comment " . $commentToMake . '<br>';
						}
					}
					catch(FacebookApiException $e)
					{
						$Result = $e->getResult();
						if($Result['error']['code'] == 17 || $Result['error']['code'] == 4)
						{
							echo "api call rate limit reached<br>";
							sleep(2);
							$ret_id = $facebook->api($post_id.'/comments','POST',$args);
							if($ret_id != 0)
							{
								echo "Success: Made Comment " . $commentToMake . '<br>';
							}
						}
					}
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