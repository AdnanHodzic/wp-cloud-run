<?php

class Creare_Twitter
{
	
	public $screen_name = "crearegroup";
	public $not = 1;
	public $cachefile = "twitter.txt";
	public $consumerkey = "XXXX";
	public $consumersecret = "XXXX";
	public $accesstoken = "XXXX";
	public $accesstokensecret = "XXXX";
	
	public $tags = true;
	public $nofollow = true;
	public $newwindow = true;
	public $hashtags = true;
	public $attags = true;
	
	private function cleanTwitterName($twitterid)
	{
		$test = substr($twitterid,0,1);
		
		if($test == "@"){
			$twitterid = substr($twitterid,1);	
		}
		
		return $twitterid;
		
    }
    
    private function changeLink($string)
	{
		if(!$this->tags){
			$string = strip_tags($string);
		}
		if($this->nofollow){
			$string = str_replace('<a ','<a rel="nofollow noopener" ', $string);	
		}
		if($this->newwindow){
			$string = str_replace('<a ','<a target="_blank" ', $string);	
		}
  		return $string;
 	}
	
	private function getTimeAgo($time)
	{
		   	$tweettime = strtotime($time); // This is the value of the time difference - UK + 1 hours (3600 seconds)
		   	$nowtime = time();
		   	$timeago = ($nowtime-$tweettime);
		   	$thehours = floor($timeago/3600);
		   	$theminutes = floor($timeago/60);
		   	$thedays = floor($timeago/86400);
  			/********************* Checking the times and returning correct value */
		   	if($theminutes < 60){
				if($theminutes < 1){
					$timemessage =  esc_html__("Less than 1 minute ago", 'rein');
				} else if($theminutes == 1) {
				 	$timemessage = sprintf( esc_html__("%s minute ago", 'rein'), $theminutes);
				} else {
				 	$timemessage = sprintf( esc_html__("%s minutes ago", 'rein'), $theminutes);
				}
			} else if($theminutes > 60 && $thedays < 1){
				 if($thehours == 1){
				 	$timemessage = sprintf( esc_html__("%s hour ago", 'rein'), $thehours);
				 } else {
				 	$timemessage = sprintf( esc_html__("%s hours ago", 'rein'), $thehours);
				 }
			} else {
				 if($thedays == 1){
				 	$timemessage = sprintf( esc_html__("%s day ago", 'rein'), $thedays);
				 } else {
				 	$timemessage = sprintf( esc_html__("%s days ago", 'rein'), $thedays);
				 }
			}
		return $timemessage;	
    }
	
	private function removeSpamCharacters($string)
	{
		$string = preg_replace('/[^(\x20-\x7F)]*/','', $string);
		return $string;
	}
	
	public function getTweets($tweets)
	{
		$t = array();
		$i = 0;
		foreach($tweets as $tweet)
		{	
			// if(isset($tweet->retweeted_status)){
			// 	$text = $this->removeSpamCharacters($tweet->retweeted_status->text);
			// } else {
			// 	$text = $this->removeSpamCharacters($tweet->text);
            // }
            $text = $tweet->text;
            $urls = array();            
            $urls[] = $tweet->entities->urls;
			$mentions = $tweet->entities->user_mentions;
			$hashtags = $tweet->entities->hashtags;
			if($urls){
				// foreach($urls as $url){
				// 	if(strpos($text,$url->url) !== false){
				// 		$text = str_replace($url->url,'<a href="'.$url->url.'">'.$url->url.'</a>',$text);	
				// 	}
                // }
                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
            preg_match_all($reg_exUrl, $text, $matches);
            $usedPatterns = array();
            foreach($matches[0] as $pattern){
                if(!array_key_exists($pattern, $usedPatterns)){
                    $usedPatterns[$pattern]=true;
                    $text = str_replace  ($pattern, '<a href="'.$pattern.'">'.$pattern.'</a>', $text);   
                }
            }
			}
			if($mentions && $this->attags){
				foreach($mentions as $mention){
					if(strpos($text,$mention->screen_name) !== false){
						$text = str_replace("@".$mention->screen_name." ",'<a href="http://twitter.com/'.$mention->screen_name.'">@'.$mention->screen_name.'</a> ',$text);	
					}
				}
			}
			if($hashtags && $this->hashtags){
				foreach($hashtags as $hashtag){
					if(strpos($text,$hashtag->text) !== false){
						$text = str_replace('#'.$hashtag->text." ",'<a href="http://twitter.com/search?q=%23'.$hashtag->text.'">#'.$hashtag->text.'</a> ',$text);	
					}
				}
			}
			$t[$i]["tweet"] = trim($this->changeLink($text));	
			$t[$i]["time"] = trim($this->getTimeAgo($tweet->created_at));
			$i++;
		}
		
		$this->saveCachedTweets($t);
		return $t;
	}
	
	private function saveCachedTweets($data)
	{
		$data = json_encode($data);
		$f = @file_put_contents($this->cachefile, $data);
	}
	
	private function getCachedTweets()
	{
		return @file_get_contents($this->cachefile);	
	}

    public function getLatestTweets( $exclude_replies = '')
	{
        require_once('twitteroauth/twitteroauth.php');
		
        $twitterconn = new TwitterOAuth($this->consumerkey, $this->consumersecret, $this->accesstoken, $this->accesstokensecret);
        
        $exclude_parameter = '';
        if( $exclude_replies ){
            $exclude_parameter = 'exclude_replies=1&';
        }
        $count = absint($this->not) * 10;
 
		$latesttweets = $twitterconn->get("https://api.twitter.com/1.1/statuses/user_timeline.json?count=".$count."&".$exclude_parameter."screen_name=".$this->screen_name);		
		
		if(!isset($latesttweets->errors)){
			return $this->getTweets($latesttweets);
		} else {
            $cached_tweets = json_decode($this->getCachedTweets(), true);
            if( isset($cached_tweets) && $cached_tweets !== '' ){
                return json_decode($this->getCachedTweets(), true);
            }else{
                return "Tweets can't be loaded";
            }           
            
		}
  		
	}
	
	public function getProfileImage()
	{
		require_once('twitteroauth/twitteroauth.php');
		
		$twitterconn = new TwitterOAuth($this->consumerkey, $this->consumersecret, $this->accesstoken, $this->accesstokensecret);
 
		$profile_image = $twitterconn->get("https://api.twitter.com/1.1/users/show.json?screen_name=".$this->screen_name);		
		
		if(!isset($profile_image->errors)){
			return $profile_image->profile_image_url;
		}
  		
	}
}