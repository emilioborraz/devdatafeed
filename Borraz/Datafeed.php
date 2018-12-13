<?php
/**
 * Dev data feed.
 */

namespace Borraz;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;

class Datafeed{

	const TWEET_LIMIT = 40;
	const TWITTER_SRC = 'twitter';
	const TWITTER_SCREEN_NAME = 'emilioborraz';
	const TWITTER_TIMELINE_API = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	const TWITTER_OAUTH_TOKEN_API = 'https://api.twitter.com/oauth2/token';
	const TWITTER_API_TIMEOUT = 10.0;
	const DATA_FILENAME = 'datafeed.json';

	public function get(){
		$dataFileName = PUBLIC_PATH . '/' . self::DATA_FILENAME;
		if(file_exists($dataFileName)){

			$handle = fopen($dataFileName, "r");
			$contents = fread($handle, filesize($dataFileName));
			fclose($handle);

			$headers = ['Content-Type' => 'application/json', 
				'Accept' => 'application/json',
				'Access-Control-Allow-Origin' => '*'];
			$response = new Response($contents, Response::HTTP_OK, $headers);
		}else{
			$response = new Response('No data available', Response::HTTP_NO_CONTENT);
		}
		$response->send();
	}

	public function refresh(){
		$tweetsDownload = $this->getTweets(self::TWEET_LIMIT);
		$tweetsDownload->then(
			function($parsedTweets){
				$this->writeToFile(PUBLIC_PATH . '/' . constant(__CLASS__ . "::DATA_FILENAME"),
					json_encode($parsedTweets));
			}
		);
		$tweetsDownload->wait();
	}
	/**
	 * Gets the latest tweets
	 * @return GuzzleHttp\Promise\Promise
	 * @throws GuzzleHttp\Exception\RequestException
	 */
	private function getTweets($limit){
		$client = new Client([
		    'base_uri' => self::TWITTER_TIMELINE_API.'?screen_name='. self::TWITTER_SCREEN_NAME .'&count='.$limit,
		    'timeout'  => self::TWITTER_API_TIMEOUT,
		]);

		$twitterPromiseRequest = $client->requestAsync('GET', '',
			['headers' => ['Authorization' => 'Bearer ' . $this->getTwitterAccessToken()]]);
		
		return $twitterPromiseRequest->then(
		    function (ResponseInterface $res){
		    	return $this->parseTweets($res->getBody()->getContents());
		    },
		    function (RequestException $e) {
		        echo $e->getMessage() . "\n";
		        echo $e->getRequest()->getMethod();
		    }
		);
	}
	private function getTwitterAccessToken(){
		$encodedKeys = urlencode(getenv('TWITTER_API_KEY')) . ':' . urlencode(getenv('TWITTER_API_KEY_SECRET'));
		$base64EncodedKeys = base64_encode($encodedKeys);

		$client = new Client([
		    'base_uri' => self::TWITTER_OAUTH_TOKEN_API,
		    'timeout'  => self::TWITTER_API_TIMEOUT,
		]);
		$response = $client->request('POST', '',
			[
				'headers' => ['Content-Type' => 'application/x-www-form-urlencoded',
							'Authorization' => 'Basic '. $base64EncodedKeys],
				'body' => 'grant_type=client_credentials'
			]);
		if(!$jsonResponse = json_decode($response->getBody()->getContents()))
			throw new Exception("Error getting Twitter token");

		return $jsonResponse->access_token;
	}
	/**
	 * Parse the Twitter API response
	 * @param  String $tweets
	 * @return array
	 */
	private function parseTweets(String $tweets){
		$tweetsArraySimplified = [];
		if(!$tweetsArray = json_decode($tweets)){
			throw new Exception("Could not decode the Twitter response", 1);
		}

		foreach ($tweetsArray as $index => $tweet) {
			array_push($tweetsArraySimplified, [
				'text' => $tweet->text,
				'thumbnail' => $this->getTweetMedia($tweet),
				'created_at' => $tweet->created_at,
				'resourceLink' => 'https://twitter.com/'.self::TWITTER_SCREEN_NAME.'/status/'.$tweet->id,
				'id' => $tweet->id,
				'source' => self::TWITTER_SRC
				]);
		}
		return $tweetsArraySimplified;
	}

	private function getTweetMedia($tweet){
		$mediaUrl = '';
		if(!empty($tweet->entities) &&
			!empty($tweet->entities->media) &&
			is_array($tweet->entities->media)){
				$firstMedia = array_pop($tweet->entities->media);
				$mediaUrl = (!empty($firstMedia->media_url_https)) ? $firstMedia->media_url_https.':thumb' : '';
		}
		return $mediaUrl;
	}

	/**
	 * Writes data to a file
	 * @param  string $filePath
	 * @param  string $data
	 * @return int | bool in case of failure
	 */
	private function writeToFile(string $filePath, string $data){
		$fp = fopen($filePath, 'wb');
		flock($fp, LOCK_EX);
		$bytesWritten = fwrite($fp, $data);
		fclose($fp);
		chmod($filePath, 0755);
		return $bytesWritten;
	}
}