<?php

namespace Drupal\twitter_feed;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\Exception\GuzzleException;

class TwitterService
{

  protected $httpClient;
  protected $configFactory;
  protected $cache;
  protected $logger;

  public function __construct(
    ClientFactory $http_client_factory,
    ConfigFactoryInterface $config_factory,
    CacheBackendInterface $cache,
    LoggerChannelFactoryInterface $logger
  ) {
    $this->httpClient = $http_client_factory->fromOptions(['timeout' => 10]);
    $this->configFactory = $config_factory;
    $this->cache = $cache;
    $this->logger = $logger->get('twitter_feed');
  }

  private function getDummyTweets()
  {
    return [
      [
        'id' => '1701357901',
        'text' => 'Just deployed our new website redesign 🚀 Check it out 👉 https://example.com #WebDev #UXDesign',
        'created_at' => '2025-04-09T12:30:00Z',
        'public_metrics' => [
          'retweet_count' => 12,
          'reply_count' => 3,
          'like_count' => 89,
          'quote_count' => 2,
        ],
        'author' => [
          'name' => 'AxionTech',
          'username' => 'axiontech',
          'profile_image_url' => 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png',
        ],
      ],
      [
        'id' => '1701357902',
        'text' => 'Big thanks to everyone who joined our webinar today 🙌 Shoutout to @saad_dev and @frontend_guru for amazing demos! #DevCommunity',
        'created_at' => '2025-04-09T10:15:00Z',
        'public_metrics' => [
          'retweet_count' => 7,
          'reply_count' => 5,
          'like_count' => 64,
          'quote_count' => 1,
        ],
        'author' => [
          'name' => 'AxionTech Events',
          'username' => 'axion_events',
          'profile_image_url' => 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png'
        ],
      ],
      [
        'id' => '1701357903',
        'text' => 'Hot tip 🔥: Use `aria-label` and `aria-hidden` wisely for better accessibility in custom components. More on this → https://a11y.axion.dev #a11y #Accessibility',
        'created_at' => '2025-04-08T22:45:00Z',
        'public_metrics' => [
          'retweet_count' => 15,
          'reply_count' => 4,
          'like_count' => 102,
          'quote_count' => 3,
        ],
        'author' => [
          'name' => 'AxionTech Devs',
          'username' => 'axion_devs',
          'profile_image_url' => 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png',
        ],
      ],
      [
        'id' => '1701357904',
        'text' => 'Coffee ☕ + Code 💻 = Perfect morning. What’s your go-to dev setup? #DevLife #MondayMotivation',
        'created_at' => '2025-04-08T08:20:00Z',
        'public_metrics' => [
          'retweet_count' => 9,
          'reply_count' => 6,
          'like_count' => 75,
          'quote_count' => 1,
        ],
        'author' => [
          'name' => 'Axion Community',
          'username' => 'axion_community',
          'profile_image_url' => 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png',
        ],
      ],
      [
        'id' => '1701357905',
        'text' => 'We just crossed 5,000 followers! 🎉 Thanks for being part of our journey 🙏 #Milestone #ThankYou',
        'created_at' => '2025-04-07T17:05:00Z',
        'public_metrics' => [
          'retweet_count' => 20,
          'reply_count' => 10,
          'like_count' => 150,
          'quote_count' => 5,
        ],
        'author' => [
          'name' => 'AxionTech',
          'username' => 'axiontech',
          'profile_image_url' => 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png',
        ],
      ],
    ];
  }


  public function getLatestTweets($handle, $count = 5)
  {
    $config = $this->configFactory->get('twitter_feed.settings');

    if ($config->get('dev_mode')) {
      $this->logger->info('Dev mode is enabled, using dummy tweets.');
      return $this->getDummyTweets();
    }

    $token = $config->get('bearer_token');

    if (empty($token) || empty($handle)) {
      $this->logger->warning('Missing bearer token or Twitter handle.');
      return [];
    }

    $cache_id = 'twitter_feed:' . $handle . ':' . $count;
    $cached = $this->cache->get($cache_id);
    if ($cached) {
      return $cached->data;
    }

    try {
      // ✅ Step 1: Get user details (not just ID)
      $userResponse = $this->httpClient->request(
        'GET',
        "https://api.twitter.com/2/users/by/username/{$handle}",
        [
          'headers' => [
            'Authorization' => "Bearer {$token}",
          ],
          'query' => [
            'user.fields' => 'name,username,profile_image_url',
          ],
        ]
      );
      $userData = json_decode($userResponse->getBody(), TRUE);

      if (!isset($userData['data']['id'])) {
        $this->logger->error('User data not found for Twitter handle: @handle', ['@handle' => $handle]);
        return [];
      }

      $userId = $userData['data']['id'];
      $author = [
        'name' => $userData['data']['name'] ?? '',
        'username' => $userData['data']['username'] ?? '',
        'profile_image_url' => $userData['data']['profile_image_url'] ?? '',
      ];

      // ✅ Step 2: Get tweets
      $tweetsResponse = $this->httpClient->request(
        'GET',
        "https://api.twitter.com/2/users/{$userId}/tweets",
        [
          'headers' => [
            'Authorization' => "Bearer {$token}",
          ],
          'query' => [
            'max_results' => $count,
            'tweet.fields' => 'created_at,public_metrics',
          ],
        ]
      );
      $tweetsData = json_decode($tweetsResponse->getBody(), TRUE);

      if (!isset($tweetsData['data'])) {
        $this->logger->warning('No tweets found for Twitter user: @user', ['@user' => $handle]);
        return [];
      }

      // ✅ Attach author info to each tweet
      $tweets = array_map(function ($tweet) use ($author) {
        $tweet['author'] = $author;
        return $tweet;
      }, $tweetsData['data']);

      $this->cache->set($cache_id, $tweets, time() + 3600);
      return $tweets;

    } catch (GuzzleException $e) {
      if ($e->getCode() === 429) {
        $this->logger->warning('Twitter API rate limit hit (429). Temporarily backing off.');
      }
      $this->logger->error('Guzzle error: @message', ['@message' => $e->getMessage()]);
    } catch (\Exception $e) {
      $this->logger->error('Unexpected error: @message', ['@message' => $e->getMessage()]);
    }

    return [];
  }
}