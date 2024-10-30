<?php

require_once('DepositClientConfig.php');
require_once('RpcParams.php');

/**
 * Interface to the DepositPhotos RPC API client.
 *
 * @copyright   Copyright (c) 2010 DepositPhotos Inc.
 */
class DepositClient
{
    const VERSION = '0.1';

    /**
     * DepositPhotos RPC uri.
     *
     * @var     string
     */
    protected $apiUrl;

    /**
     * DepositPhotos API key.
     *
     * @var     string
     */
    protected $apiKey;

    /**
     * Deposit API session id.
     *
     * @var     string
     */
    protected $sessionId;

    /**
     * Deposit API session name.
     *
     * @var     string
     */
    protected $sessionName;

    protected $last_response;


    /**
     * Constructor
     *
     * @param   string $apiUrl  DepositPhotos RPC uri
     * @param   string $apiKey  DepositPhotos API key
     */
    public function  __construct($apiUrl, $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    public function getLastResponse()
    {
        return $this->decodeResponse($this->last_response);
    }

    /**
     * Returns info about available funds on currently logged account
     */

    public function availableFunds()
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::AVAILABLE_FUNDS_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId(),
        );

        return $this->checkResponse($this->post($this->apiUrl, $postParams));
    }

    /**
     * This method makes possible to search media in the DepositPhotos image bank.
     *
     * The $criteria array must conform to the following format:
     * <code>
     * array(
     *  // all params are optional
     *  RpcParams::SEARCH_QUERY  => 'query string',
     *  RpcParams::SEARCH_SORT   => 'sort result by one of 1-6 sort types',
     *                              // WEIGHT       = 1;
     *                              // DOWNLOADS    = 2;
     *                              // POPULARITY   = 3;
     *                              // BEST_SALES   = 4;
     *                              // TIME         = 5;
     *                              // TIME_DESC    = 6;
     *  RpcParams::SEARCH_LIMIT  => 'limit of results to return',
     *  RpcParams::SEARCH_OFFSET => 'results offset', // if used without RpcParams::SEARCH_LIMIT, then ignored
     *  RpcParams::SEARCH_CATEGORIES => 'list of categories id's separated by whitspace',
     *  RpcParams::SEARCH_COLOR  => 'search by one of 1-17 colors',
     *  RpcParams::SEARCH_NUDITY => 'if true, exclude nudity photos',
     *  RpcParams::SEARCH_EXTENDED   => 'if true, include only with extended license',
     *  RpcParams::SEARCH_EXCLUSIVE  => 'if true, include only with exclusive',
     *  RpcParams::SEARCH_USER   => 'media author id',
     *  RpcParams::SEARCH_DATE1  => 'include results from date',
     *  RpcParams::SEARCH_DATE2  => 'include results to date',
     *  RpcParams::SEARCH_ORIENTATION=> 'image orientation, can be one of RpcParams::ORIENT_* constants',
     *  RpcParams::SEARCH_IMAGESIZE  => 'image size, can be one of RpcParams::SIZE_* constants',
     *  RpcParams::SEARCH_VECTOR => 'if true, include only vector media',
     *  RpcParams::SEARCH_PHOTO  => 'if true, include only photo media' // if both, vector and photo media is true, returns all media
     * )
     * </code>
     *
     * Response format:
     * <code>
     * array(
     *  0 => stdClass(
     *      id -> 'media id'
     *      url -> 'small tumbnail url'
     *      title -> 'media title'
     *      description -> 'media description'
     *      userid -> 'author id'
     *  )
     *  [, 1 -> ...]
     * )
     * </code>
     *
     * @param   array $criteria
     * @return  stdClass
     */
    public function search($criteria = array())
    {
        $postParams = array(
            RpcParams::APIKEY   => $this->apiKey,
            RpcParams::COMMAND  => RpcParams::SEARCH_CMD);

        $postParams = array_merge($postParams, $criteria);

        return $this->checkResponse($this->post($this->apiUrl, $postParams));
    }

    /**
     * This method returns list of categories used on the DepositPhotos website.
     *
     * Response format:
     * <code>
     * stdClass(
     *  category_id -> 'category title'
     *  [, category_id -> ...]
     * )
     * </code>
     *
     * @return  stdClass
     */
    public function getCategoriesList()
    {
        $postParams = array(
            RpcParams::APIKEY   => $this->apiKey,
            RpcParams::COMMAND  => RpcParams::GET_CATEGORIES_CMD);

        return $this->checkResponse($this->post($this->apiUrl, $postParams))->result;
    }

    /**
     * This method return all information about a media.
     *
     * Response format:
     * <code>
     * stdClass(
     *  id -> 'media id'
     *  userid -> 'author id'
     *  username -> 'author name'
     *  title -> 'media title'
     *  description -> 'media description'
     *  published -> 'publish date'
     *  isextended -> 'is extended license'
     *  isexclusive -> 'is exclusive'
     *  width -> 'media width'
     *  height -> 'media height'
     *  mp -> 'media mega pixels'
     *  views -> 'count of media views'
     *  downloads -> 'count of media downloads'
     *  tags -> array( // tags associated with media
     *      0 => 'tag name'
     *      [, 1 => ...]
     *  )
     *  categories -> stdClass( // categories associated with media
     *      category_id -> 'category title'
     *      [, category_id -> ...]
     *  )
     *  url -> 'large tumbnail url'
     * )
     * </code>
     *
     * @param   integer $mediaId
     * @return  stdClass
     */
    public function getMediaData($mediaId)
    {
        $postParams = array(
            RpcParams::APIKEY   => $this->apiKey,
            RpcParams::COMMAND  => RpcParams::GET_MEDIA_DATA_CMD,
            RpcParams::MEDIA_ID => $mediaId);

        return $this->checkResponse($this->post($this->apiUrl, $postParams));
    }

    /**
     * This method returns most searched tag and most used tag on the DepositPhotos website.
     *
     * This method may help you to create a tags cloud.
     *
     * Response format:
     * <code>
     * array(
     *  0 => stdClass(
     *      rate -> 'rate of tag'
     *      tag -> 'tag name'
     *  )
     *  [, 1 => ...]
     * )
     * </code>
     *
     * @return  array
     */
    public function getTagCloud()
    {
        $postParams = array(
            RpcParams::APIKEY   => $this->apiKey,
            RpcParams::COMMAND  => RpcParams::GET_TAG_CLOUD_CMD);

        return $this->checkResponse($this->post($this->apiUrl, $postParams))->result;
    }

    /**
     * This method authenticates the API client and gives the session ID.
     *
     * NOTE: Some methods require authentication before calling.
     *
     * @param   string $user
     * @param   string $password
     * @return  array   of session id and session name
     */
    public function login($user, $password)
    {
        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::LOGIN_CMD,
            RpcParams::LOGIN_USER   => $user,
            RpcParams::LOGIN_PASSWORD=> $password);

        /* @var $result HttpRpcLogin */
        $result = $this->checkResponse($this->post($this->apiUrl, $postParams));

        $this->sessionId    = $result->sessionid;
        $this->sessionName  = $result->sessionName;

        return array($this->sessionId, $this->sessionName);
    }


    public function loginAsUser($user, $password)
    {
        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::LOGINASUSER_CMD,
            RpcParams::LOGIN_USER   => $user,
            RpcParams::LOGIN_PASSWORD=> $password);

        /* @var $result HttpRpcLogin */
        $result = $this->checkResponse($this->post($this->apiUrl, $postParams));

        $this->sessionId    = $result->sessionid;

        return $result;
    }

    /**
     * This method returns download link for specified media.
     *
     * NOTE: This method require authentication before calling.
     *
     * @param   integer $mediaId
     * @return  string
     */
    public function getMedia($mediaId, $license = RpcParams::LICENSE_STANDART, $size = RpcParams::SIZE_LARGE, $subaccountId = null, $currency = false)
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::GET_MEDIA_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId(),
            RpcParams::MEDIA_ID     => $mediaId,
            RpcParams::MEDIA_LICENSE=> $license,
            RpcParams::MEDIA_OPTION => $size,
            RpcParams::MEDIA_PURCHASE_CURRENCY => $currency);

        if (null != $subaccountId) {
            $postParams[RpcParams::SUBACC_ID] = $subaccountId;
        }

        return $this->checkResponse($this->post($this->apiUrl, $postParams))->downloadLink;
    }

    /**
     * This method creates a subaccount of reseller's account, which made the purchase.
     *
     * NOTE: This method require authentication before calling.
     *
     * @param   string $email
     * @param   string $firstName
     * @param   string $lastName
     * @param   string $company
     * @return  integer
     */
    public function createSubaccount($email, $firstName, $lastName, $company = null)
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::CREATE_SUBACCOUNT_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId(),
            RpcParams::SUBACC_EMAIL => $email,
            RpcParams::SUBACC_FNAME => $firstName,
            RpcParams::SUBACC_LNAME => $lastName);

        if (null != $company) {
            $postParams[RpcParams::SUBACC_COMPANY] = $company;
        }
        return $this->checkResponse($this->post($this->apiUrl, $postParams))->subaccountId;
    }

    /**
     * This method deletes subaccount, created by method createSubaccount.
     *
     * NOTE: This method require authentication before calling.
     *
     * @param   integer $subaccountId
     */
    public function deleteSubaccount($subaccountId)
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::DELETE_SUBACCOUNT_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId(),
            RpcParams::SUBACC_ID    => $subaccountId);

        $this->checkResponse($this->post($this->apiUrl, $postParams));
    }

    /**
     * This method updates subaccount, created by method createSubaccount.
     *
     * NOTE: This method require authentication before calling.
     *
     * @param   integer $subaccountId
     * @param   string $email
     * @param   string $firstName
     * @param   string $lastName
     * @param   string $company
     */
    public function updateSubaccount($subaccountId, $email, $firstName, $lastName, $company = null)
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::UPDATE_SUBACCOUNT_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId(),
            RpcParams::SUBACC_ID    => $subaccountId,
            RpcParams::SUBACC_EMAIL => $email,
            RpcParams::SUBACC_FNAME => $firstName,
            RpcParams::SUBACC_LNAME => $lastName);

        if (null != $company) {
            $postParams[RpcParams::SUBACC_COMPANY] = $company;
        }
        $this->checkResponse($this->post($this->apiUrl, $postParams));
    }

    /**
     * This method returns the subaccounts id's, created by reseller.
     *
     * NOTE: This method require authentication before calling.
     *
     * @param   integer $limit
     * @param   integer $offset
     * @return  array   of total subaccounts count and requested range of
     *                  subaccounts id's.
     */
    public function getSubaccounts($limit = null, $offset = null)
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::GET_SUBACCOUNTS_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId());

        if (null !== $limit) {
            $postParams[RpcParams::SUBACC_LIMIT] = $limit;
        }

        if (null !== $offset) {
            $postParams[RpcParams::SUBACC_OFFSET] = $offset;
        }

        /* @var $result HttpRpcSubaccounts */
        $result = $this->checkResponse($this->post($this->apiUrl, $postParams));

        return array($result->count, $result->subaccounts);
    }

    /**
     * This method returns the specified subaccount data.
     *
     * NOTE: This method require authentication before calling.
     *
     * @param   integer $subaccountId
     * @return  stdClass
     */
    public function getSubaccountData($subaccountId)
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::GET_SUBACCOUNT_DATA_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId(),
            RpcParams::SUBACC_ID    => $subaccountId);

        return $this->checkResponse($this->post($this->apiUrl, $postParams));
    }

    /**
     * This method returns all data about subaccount purchases.
     *
     * NOTE: This method require authentication before calling.
     *
     * @param   integer $subaccountId
     * @return  array   of total purchases count and purchases data
     */
    public function getSubaccountPurchases($subaccountId, $limit = null, $offset = null)
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::GET_SUBACCOUNT_PURCHASES_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId(),
            RpcParams::SUBACC_ID    => $subaccountId);

        if (null !== $limit) {
            $postParams[RpcParams::SUBACC_LIMIT] = $limit;
        }

        if (null !== $offset) {
            $postParams[RpcParams::SUBACC_OFFSET] = $offset;
        }

        $result = $this->checkResponse($this->post($this->apiUrl, $postParams));

        return array($result->count, $result->purchases);
    }

    /**
     * This method returns text of proof of purchase.
     *
     * NOTE: This method require authentication before calling.
     *
     * @param   integer $subaccountId
     * @param   integer $licenseId
     * @return  string
     */
    public function getLicense($subaccountId, $licenseId)
    {
        $this->checkLoggedIn();

        $postParams = array(
            RpcParams::APIKEY       => $this->apiKey,
            RpcParams::COMMAND      => RpcParams::GET_LICENSE_CMD,
            RpcParams::SESSION_ID   => $this->getSessionId(),
            RpcParams::SUBACC_ID    => $subaccountId,
            RpcParams::SUBACC_LICENSE_ID => $licenseId);

        return $this->checkResponse($this->post($this->apiUrl, $postParams))->text;
    }

    /**
     * Sets the API session id.
     */
    public function setSessionId($id)
    {
        $this->sessionId = $id;
    }

    /**
     * Returns the API session id.
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * This method returns count of stock photos, new photos by week and authors.
     *
     * Response format:
     * <code>
     * stdClass(
     *  totalFiles -> 'count of stock photos',
     *  totalWeekFiles -> 'count of new files by week',
     *  totalAuthors -> 'count of photographers'
     * )
     * </code>
     *
     * @return  stdClass
     */
    public function getInfo()
    {
        $postParams = array(
            RpcParams::APIKEY   => $this->apiKey,
            RpcParams::COMMAND  => RpcParams::GET_INFO_CMD);

        return $this->checkResponse($this->post($this->apiUrl, $postParams));
    }

    /**
     * Check whether the response is success.
     *
     * @param   string $response
     * @return  strClass
     * @throws  EResponseFail
     */
    protected function checkResponse($response)
    {
        $result = $this->decodeResponse($response);

        if (!is_object($result)) {
            throw new EResponseFail('The rpc response is empty, or have invalid format');
        }

        if ('failure' == $result->type) {
            throw new EDepositApiCall($result->errormsg, $result->errorcode);
        }

        return $result;
    }

    /**
     * Decodes response from JSON format.
     *
     * @param   string $response
     * @return  array|stdClass
     */
    protected function decodeResponse($response)
    {
        return json_decode($response);
    }

    /**
     * Sends the POST request to the API service.
     *
     * This method uses the cURL extension.
     *
     * @param   array $url
     * @param   array $parameters
     * @return  string
     */
    protected function post($url, $parameters)
    {
        return $this->last_response = $this->createHttpClient()->post($url, $parameters);
    }

    /**
     * For testing purposes.
     *
     * @return CurlHttpClient
     */
    protected function createHttpClient()
    {
        return new CurlHttpClient();
    }

    /**
     * Check whether the session id is not empty.
     *
     * @throws  EAuthenticationRequired
     */
    public function checkLoggedIn()
    {
        if (null === $this->getSessionId()) {
            throw new EAuthenticationRequired('The called method requires authentication');
        }
    }
}


/**
 * Simple HTTP client based on cURL extension.
 *
 * You may use another HTTP client with one requared method {@link post}
 */
class CurlHttpClient
{
    /**
     * The cURL resource handle.
     *
     * @var     resource
     */
    protected $ch;

    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * Sends the HTTP POST request to specified URL with given parameters.
     *
     * @param   string $url         the URL to request
     * @param   array $parameters   the POST parameters to include to request
     * @return  string              the server response
     */
    public function post($url, $parameters)
    {
        if (false === curl_setopt_array($this->ch, array(
            CURLOPT_POST            => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_URL             => $url,
            CURLOPT_POSTFIELDS      => $parameters
        ))) {
            throw new ECurlFail('Error at setting cURL options, reason: '.curl_error($this->ch), curl_errno($this->ch));
        }

        if (false === $result = curl_exec($this->ch)) {
            throw new ECurlFail('Error at execute cURL request, reason: '.curl_error($this->ch), curl_errno($this->ch));
        }

        if (200 != curl_getinfo($this->ch, CURLINFO_HTTP_CODE)) {
            throw new EServiceUnavailable('The API servise is unavailable');
        }

        return $result;
    }

    /**
     * Object destructor.
     */
    public function __destruct()
    {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }
}


class EDepositClient extends Exception {};
class EResponseFail extends EDepositClient {};
class EDepositApiCall extends EDepositClient {};
class ECurlFail extends EDepositClient {};
class EServiceUnavailable extends EDepositClient {};
class EAuthenticationRequired extends EDepositClient {};

?>
