// Worker version: 2.8.0
// Default cookie prefixes for cache bypassing
const DEFAULT_BYPASS_COOKIES = [
  'wordpress_logged_in_',
  'comment_',
  'woocommerce_',
  'wordpressuser_',
  'wordpresspass_',
  'wordpress_sec_',
  'yith_wcwl_products',
  'edd_items_in_cart',
  'it_exchange_session_',
  'comment_author',
  'dshack_level',
  'auth',
  'noaffiliate_',
  'mp_session',
  'mp_globalcart_',
  'xf_'
]

// Third party query parameter that we need to ignore in a URL
const THIRD_PARTY_QUERY_PARAMETERS = [
  'Browser',
  'C',
  'GCCON',
  'MCMP',
  'MarketPlace',
  'PD',
  'Refresh',
  'Sens',
  'ServiceVersion',
  'Source',
  'Topic',
  '__WB_REVISION__',
  '__cf_chl_jschl_tk__',
  '__d',
  '__hsfp',
  '__hssc',
  '__hstc',
  '__s',
  '_branch_match_id',
  '_bta_c',
  '_bta_tid',
  '_com',
  '_escaped_fragment_',
  '_ga',
  '_ga-ft',
  '_gl',
  '_hsmi',
  '_ke',
  '_kx',
  '_paged',
  '_sm_byp',
  '_sp',
  '_szp',
  '3x',
  'a',
  'a_k',
  'ac',
  'acpage',
  'action-box',
  'action_object_map',
  'action_ref_map',
  'action_type_map',
  'activecampaign_id',
  'ad',
  'ad_frame_full',
  'ad_frame_root',
  'ad_name',
  'adclida',
  'adid',
  'adlt',
  'adsafe_ip',
  'adset_name',
  'advid',
  'aff_sub2',
  'afftrack',
  'afterload',
  'ak_action',
  'alt_id',
  'am',
  'amazingmurphybeds',
  'amp;',
  'amp;amp',
  'amp;amp;amp',
  'amp;amp;amp;amp',
  'amp;utm_campaign',
  'amp;utm_medium',
  'amp;utm_source',
  'ampStoryAutoAnalyticsLinker',
  'ampstoryautoanalyticslinke',
  'an',
  'ap',
  'ap_id',
  'apif',
  'apipage',
  'as_occt',
  'as_q',
  'as_qdr',
  'askid',
  'atFileReset',
  'atfilereset',
  'aucid',
  'auct',
  'audience',
  'author',
  'awt_a',
  'awt_l',
  'awt_m',
  'b2w',
  'back',
  'bannerID',
  'blackhole',
  'blockedAdTracking',
  'blog-reader-used',
  'blogger',
  'br',
  'bsft_aaid',
  'bsft_clkid',
  'bsft_eid',
  'bsft_ek',
  'bsft_lx',
  'bsft_mid',
  'bsft_mime_type',
  'bsft_tv',
  'bsft_uid',
  'bvMethod',
  'bvTime',
  'bvVersion',
  'bvb64',
  'bvb64resp',
  'bvplugname',
  'bvprms',
  'bvprmsmac',
  'bvreqmerge',
  'cacheburst',
  'campaign',
  'campaign_id',
  'campaign_name',
  'campid',
  'catablog-gallery',
  'channel',
  'checksum',
  'ck_subscriber_id',
  'cmplz_region_redirect',
  'cmpnid',
  'cn-reloaded',
  'code',
  'comment',
  'content_ad_widget',
  'cost',
  'cr',
  'crl8_id',
  'crlt.pid',
  'crlt_pid',
  'crrelr',
  'crtvid',
  'ct',
  'cuid',
  'daksldlkdsadas',
  'dcc',
  'dfp',
  'dm_i',
  'domain',
  'dosubmit',
  'dsp_caid',
  'dsp_crid',
  'dsp_insertion_order_id',
  'dsp_pub_id',
  'dsp_tracker_token',
  'dt',
  'dur',
  'durs',
  'e',
  'ee',
  'ef_id',
  'el',
  'env',
  'erprint',
  'et_blog',
  'exch',
  'externalid',
  'fb_action_ids',
  'fb_action_types',
  'fb_ad',
  'fb_source',
  'fbclid',
  'fbzunique',
  'fg-aqp',
  'fireglass_rsn',
  'fo',
  'fp_sid',
  'fpa',
  'fref',
  'fs',
  'furl',
  'fwp_lunch_restrictions',
  'ga_action',
  'gclid',
  'gclsrc',
  'gdffi',
  'gdfms',
  'gdftrk',
  'gf_page',
  'gidzl',
  'goal',
  'gooal',
  'gpu',
  'gtVersion',
  'haibwc',
  'hash',
  'hc_location',
  'hemail',
  'hid',
  'highlight',
  'hl',
  'home',
  'hsa_acc',
  'hsa_ad',
  'hsa_cam',
  'hsa_grp',
  'hsa_kw',
  'hsa_mt',
  'hsa_net',
  'hsa_src',
  'hsa_tgt',
  'hsa_ver',
  'ias_campId',
  'ias_chanId',
  'ias_dealId',
  'ias_dspId',
  'ias_impId',
  'ias_placementId',
  'ias_pubId',
  'ical',
  'ict',
  'ie',
  'igshid',
  'im',
  'ipl',
  'jw_start',
  'jwsource',
  'k',
  'key1',
  'key2',
  'klaviyo',
  'ksconf',
  'ksref',
  'l',
  'label',
  'lang',
  'ldtag_cl',
  'level1',
  'level2',
  'level3',
  'level4',
  'limit',
  'lng',
  'load_all_comments',
  'lt',
  'ltclid',
  'ltd',
  'lucky',
  'm',
  'm?sales_kw',
  'matomo_campaign',
  'matomo_cid',
  'matomo_content',
  'matomo_group',
  'matomo_keyword',
  'matomo_medium',
  'matomo_placement',
  'matomo_source',
  'max-results',
  'mc_cid',
  'mc_eid',
  'mdrv',
  'mediaserver',
  'memset',
  'mibextid',
  'mkcid',
  'mkevt',
  'mkrid',
  'mkwid',
  'ml_subscriber',
  'ml_subscriber_hash',
  'mobileOn',
  'mode',
  'month',
  'msID',
  'msclkid',
  'msg',
  'mtm_campaign',
  'mtm_cid',
  'mtm_content',
  'mtm_group',
  'mtm_keyword',
  'mtm_medium',
  'mtm_placement',
  'mtm_source',
  'murphybedstoday',
  'mwprid',
  'n',
  'native_client',
  'navua',
  'nb',
  'nb_klid',
  'o',
  'okijoouuqnqq',
  'org',
  'pa_service_worker',
  'partnumber',
  'pcmtid',
  'pcode',
  'pcrid',
  'pfstyle',
  'phrase',
  'pid',
  'piwik_campaign',
  'piwik_keyword',
  'piwik_kwd',
  'pk_campaign',
  'pk_keyword',
  'pk_kwd',
  'placement',
  'plat',
  'platform',
  'playsinline',
  'pp',
  'pr',
  'prid',
  'print',
  'q',
  'q1',
  'qsrc',
  'r',
  'rd',
  'rdt_cid',
  'redig',
  'redir',
  'ref',
  'reftok',
  'relatedposts_hit',
  'relatedposts_origin',
  'relatedposts_position',
  'remodel',
  'replytocom',
  'reverse-paginate',
  'rid',
  'rnd',
  'rndnum',
  'robots_txt',
  'rq',
  'rsd',
  's_kwcid',
  'sa',
  'safe',
  'said',
  'sales_cat',
  'sales_kw',
  'sb_referer_host',
  'scrape',
  'script',
  'scrlybrkr',
  'search',
  'sellid',
  'sersafe',
  'sfn_data',
  'sfn_trk',
  'sfns',
  'sfw',
  'sha1',
  'share',
  'shared',
  'showcomment',
  'si',
  'sid',
  'sid1',
  'sid2',
  'sidewalkShow',
  'sig',
  'site',
  'site_id',
  'siteid',
  'slicer1',
  'slicer2',
  'source',
  'spref',
  'spvb',
  'sra',
  'src',
  'srk',
  'srp',
  'ssp_iabi',
  'ssts',
  'stylishmurphybeds',
  'subId1 ',
  'subId2 ',
  'subId3',
  'subid',
  'swcfpc',
  'tail',
  'teaser',
  'test',
  'timezone',
  'toWww',
  'triplesource',
  'trk_contact',
  'trk_module',
  'trk_msg',
  'trk_sid',
  'tsig',
  'turl',
  'u',
  'up_auto_log',
  'upage',
  'updated-max',
  'uptime',
  'us_privacy',
  'usegapi',
  'usqp',
  'utm',
  'utm_campa',
  'utm_campaign',
  'utm_content',
  'utm_expid',
  'utm_id',
  'utm_medium',
  'utm_reader',
  'utm_referrer',
  'utm_source',
  'utm_sq',
  'utm_ter',
  'utm_term',
  'v',
  'vc',
  'vf',
  'vgo_ee',
  'vp',
  'vrw',
  'vz',
  'wbraid',
  'webdriver',
  'wing',
  'wpdParentID',
  'wpmp_switcher',
  'wref',
  'wswy',
  'wtime',
  'x',
  'zMoatImpID',
  'zarsrc',
  'zeffdn'
]

// List of Static File Extensions for which we don't need to run the whole logic
// Just fetch them and send the response
const STATIC_FILE_EXTENSIONS = [
  '.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp', '.avif', '.tiff', '.ico', '.3gp', '.wmv', '.avi', '.asf', '.asx', '.mpg', '.mpeg', '.webm', '.ogg', '.ogv', '.mp4', '.mkv', '.pls', '.mp3', '.mid', '.wav', '.swf', '.flv', '.exe', '.zip', '.tar', '.rar', '.gz', '.tgz', '.bz2', '.uha', '.7z', '.doc', '.docx', '.pdf', '.iso', '.test', '.bin', '.js', '.json', '.css', '.eot', '.ttf', '.woff', '.woff2', '.webmanifest'
]

/**
 * Function to check if the response status code is within the range 
 * of 3XX, 4XX, 5XX and if so, then return TRUE else FALSE
 *
 * @param {Response} response - The origin server response
 * @return {Boolean} has_unusual_response_code - If the response has a status code is 
 * within the defined list then return TRUE else FALSE
 */
function has_unusual_origin_server_response_code(response) {
  const responseStatusCode = String( response?.status )

  if( responseStatusCode.startsWith( '3' ) || responseStatusCode.startsWith( '4' ) || responseStatusCode.startsWith( '5' ) ) {
    response.headers?.set('x-wp-cf-super-cache-worker-origin-response', responseStatusCode)
    return true
  } else {
    return false
  }
}

/**
 * Function to normalize the URL by removing promotional query parameters from the URL and cache the original URL
 * @param {Object} event - Event Object
 * @return {URL} reqURL - Request URL without promotional query strings
 */
function url_normalize(event) {
  try {
    // Fetch the Request URL from the event
    // Parse the URL for better handling
    const reqURL = new URL(event?.request?.url)

    // Loop through the promo queries (THIRD_PARTY_QUERY_PARAMETERS) and see if we have any of these queries present in the URL, if so remove them
    for ( let i = 0; i < THIRD_PARTY_QUERY_PARAMETERS.length; i++ ) {

      // Create the REGEX to text the URL with our desired parameters
      const promoUrlQuery = new RegExp( '(&?)(' + THIRD_PARTY_QUERY_PARAMETERS[i] + '=\\S+)', 'g' )

      // Check if the reqURL.search has these search query parameters
      if(promoUrlQuery.test( reqURL.search )) {

        // The URL has promo query parameters that we need to remove
        const urlSearchParams = reqURL.searchParams

        urlSearchParams.delete( THIRD_PARTY_QUERY_PARAMETERS[i] )
      }
    }

    return reqURL

  } catch (err) {
    return {
      error: true,
      errorMessage: `URL Handling Error: ${err.message}`,
      errorStatusCode: 400
    }
  }
}

/**
 * Function to check if the current request should be BYPASSed or Cached based on exclusion cookies
 * entered by the user in the plugin settings
 * @param {String} cookieHeader - The cookie header of the current request
 * @param {Array} cookies_list - List of cookies which should not be cached
 * @return {Boolean} blackListedCookieExists - If blacklisted cookie exists in the current request
 */
function are_blacklisted_cookies(cookieHeader, cookies_list) {
  let blackListedCookieExists = false

  // Make sure both cookieHeader & cookies_list are defined & the length of both cookieHeader & cookies_list > 0
  if (
    cookieHeader?.length > 0 &&
    cookies_list?.length > 0
  ) {
    // Split the received request cookie header by semicolon to an Array
    const cookies = cookieHeader.split(';')

    // Loop through the cookies in the request header and check if there is any cookie present there
    // which is also mentioned in our bypassed cookies array
    // if there is then set blackListedCookieExists as true and break out of the loops
    for ( let i = 0; i < cookies.length; i++ ) {

      for ( let j = 0; j < cookies_list.length; j++ ) {

        if (cookies[i].trim().includes(cookies_list[j].trim())) {
          blackListedCookieExists = true

          // Found item. Break out from the loop
          break
        }
      }

      // Check if blackListedCookieExists is true then break out of this loop. Else continue the loop
      if( blackListedCookieExists ) {
        break
      }
    }
  }

  return blackListedCookieExists // value -> TRUE | FALSE
}

/**
 * Function to add extra response headers for BYPASSed Requests
 * @param {Response} res - The response object
 * @param {String} reason - The string that hold the bypass reason
 */
function add_bypass_custom_headers(res, reason) {
  if (res && (reason?.length > 0)) {
    // BYPASS the request and add our custom headers
    res?.headers?.set('x-wp-cf-super-cache-worker-status', 'bypass')
    res?.headers?.set('x-wp-cf-super-cache-worker-bypass-reason', reason)
    res?.headers?.set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
  }
}

/**
 * The function that handles the Request
 * @param {Object} event - Received Event object
 * @return {Response} response - Response object that is being returned to the user
 */
async function handleRequest(event) {

  const request = event?.request
  const requestURL = url_normalize(event)

  // Check if we have received any error in the url_normalize() call, if so return that error message
  if( requestURL?.error ) {
    return new Response( 
      requestURL.errorMessage,
      { status: requestURL.errorStatusCode, statusText: requestURL.errorMessage } 
    )
  }

  let response = false
  let bypassCache = false
  const bypassReason = {
    'req_method': false,
    'admin_req': false,
    'file_path_ext': false,
    'page_excluded': false,
    'file_excluded': false,
    'cookie': false
  }
  let bypassReasonDetails = ''
  const cookieHeader = request?.headers?.get('cookie')
  const reqDetails = {
    'contentTypeHTML': false
  }

  // ---------------------------------------------------------
  // Check - If the request is for an static file, 
  // then no need to go further, just fetch the file and return
  // ---------------------------------------------------------
  const requestPath = requestURL?.pathname
  let isStaticFile = false

  // Loop through the STATIC_FILE_EXTENSIONS and check if the request path has any of the extensions
  for ( let i = 0; i < STATIC_FILE_EXTENSIONS.length; i++ ) {
    if( requestPath.endsWith( STATIC_FILE_EXTENSIONS[i] ) ) {
      // Set isStaticFile to TRUE and break out of the loop
      isStaticFile = true

      // Found item. Break out from the loop
      break
    }
  }

  if( isStaticFile ) {
    let staticFileResponse

    try {
      staticFileResponse = await fetch(request)
    } catch (err) {
      return new Response( 
        `Error: ${err.message}`,
        { status: 500, statusText: "Unable to fetch the static file from the origin server" } 
      )
    }

    return new Response(staticFileResponse?.body, staticFileResponse)
  }

  // ---------------------------------------------------------
  // Check - Bypass Request ? - Only Based on Request Headers
  // ---------------------------------------------------------

  // 1. BYPASS any requests whose request method is not GET or HEAD
  const allowedReqMethods = ['GET', 'HEAD']
  if (!bypassCache && request) {
    if (!allowedReqMethods.includes(request?.method)) {
      bypassCache = true
      bypassReason.req_method = true
      bypassReasonDetails = `Caching not possible for req method ${request.method}`
    }
  }

  // 2. BYPASS the cache for WP Admin HTML Requests & Any File That has /wp-admin/ in it & API endpoints
  // Get the Accept header of the request being received by the CF Worker
  const accept = request?.headers?.get('Accept')

  if (!bypassCache && accept) {

    // List of path regex that we will BYPASS caching
    // Path includes - WP Admin Paths, WP REST API, WooCommerce API, EDD API Endpoints
    const bypass_admin_path = new RegExp(/(\/(wp-admin)(\/?))/g)
    const bypass_cache_paths = new RegExp(/(\/((wp-admin)|(wc-api)|(edd-api))(\/?))/g)

    // List of file extensions to be BYPASSed
    const bypass_file_ext = new RegExp(/\.(xsl|xml)$/)

    // Check if the request is for WP Admin endpoint & accept type includes text/html i.e. the main HTML request
    if ( accept?.includes('text/html') ) {
      reqDetails.contentTypeHTML = true
    }

    // Check if the request URL is an admin URL for HTML type requests
    if ( reqDetails.contentTypeHTML && bypass_admin_path.test(requestPath) ) {
      bypassCache = true
      bypassReason.admin_req = true
      bypassReasonDetails = 'WP Admin HTML request'

    } else if ( bypass_cache_paths.test(requestPath) || bypass_file_ext.test(requestPath) ) {
      // This is for files which starts with /wp-admin/ but not supposed to be cached
      // E.g. /wp-admin/load-styles.php || /wp-admin/admin-ajax.php
      // Also API endpoints and xml/xsl files to ensure sitemap isn't cached

      bypassCache = true
      bypassReason.file_path_ext = true
      bypassReasonDetails = 'Dynamic File'
    }
  }

  // 3. BYPASS the cache if DEFAULT_BYPASS_COOKIES is present in the request
  // AND also only for the HTML type requests
  if (
    !bypassCache &&
    reqDetails.contentTypeHTML &&
    cookieHeader?.length > 0 &&
    DEFAULT_BYPASS_COOKIES.length > 0
  ) {

    // Separate the request cookies by semicolon and create an Array
    const cookies = cookieHeader.split(';')

    // Loop through the cookies Array to see if there is any cookies present that is present in DEFAULT_BYPASS_COOKIES
    let foundDefaultBypassCookie = false

    for ( let i = 0; i < cookies.length; i++ ) {

      for ( let j = 0; j < DEFAULT_BYPASS_COOKIES.length; j++ ) {

        if ( cookies[i].trim().startsWith( DEFAULT_BYPASS_COOKIES[j].trim() ) ) {
          bypassCookieName = cookies[i].trim().split('=')
          bypassCache = true
          bypassReason.cookie = true
          bypassReasonDetails = `Default Bypass Cookie [${bypassCookieName[0]}] Present`
          foundDefaultBypassCookie = true

          // Stop the loop
          break
        }
      }

      // Stop the loop if foundDefaultBypassCookie is TRUE else continue
      if( foundDefaultBypassCookie ) {
        break
      }
    }
  }

  /**
   * Check if the Request has been Bypassed so far.
   * If not, then check if the request exists in CF Edge Cache & if it does, send it
   * If it does not exists in CF Edge Cache, then check if the request needs to be Bypassed based on the headers
   * present in the Response.
   */
  if (!bypassCache) { // bypassCache is still FALSE

    // Check if the Request present in the CF Edge Cache
    const cacheKey = new Request(requestURL, request)
    const cache = caches?.default // Get global CF cache object for this zone

    // Try to Get this request from this zone's cache
    try {
      response = await cache?.match(cacheKey)
    } catch (err) {
      return new Response( 
        `Error: ${err.message}`,
        { status: 500, statusText: "Unable to fetch cache from Cloudflare" } 
      )
    }

    if (response) { // Cache is present for this request in the CF Edge. Nothing special needs to be done.

      // This request is already cached in the CF Edge. So, simply create a response and set custom headers
      response = new Response(response?.body, response)
      response?.headers?.set('x-wp-cf-super-cache-worker-status', 'hit')

    } else { // Cache not present in CF Edge. Check if Req needs to be Bypassed or Cached based on Response header data

      // Fetch the response of this given request normally without any special parameters
      // so that we can use the response headers set by the plugin at the server level
      let fetchedResponse
      try {
        fetchedResponse = await fetch(request)
      } catch(err) {
        return new Response( 
          `Error: ${err.message}`,
          { status: 500, statusText: "Unable to fetch content from the origin server" } 
        )
      }

      // If the above if check fails that means we have a good response and lets proceed
      response = new Response(fetchedResponse.body, fetchedResponse)

      // Check if the response has any unusual origin server response code & if so then return the response
      if( has_unusual_origin_server_response_code(response) ) {
        return response
      }

      // ---------------------------------------------------------
      // Check - Bypass Request ? - Based on RESPONSE Headers
      // ---------------------------------------------------------

      // 4. BYPASS the HTML page requests which are excluded from caching (via WP Admin plugin settings or page level settings)
      if (
        !bypassCache &&
        response?.headers?.get('content-type')?.includes('text/html') &&
        !response?.headers?.has('x-wp-cf-super-cache-active')
      ) {
        bypassCache = true
        bypassReason.page_excluded = true
        bypassReasonDetails = 'This page is excluded from caching'
      }

      // 5. BYPASS the static files (non HTML) which has x-wp-cf-super-cache response header set to no-cache
      if (!bypassCache &&
        !response?.headers?.get('content-type')?.includes('text/html') &&
        (response?.headers?.get('x-wp-cf-super-cache') === 'no-cache')
      ) {
        bypassCache = true
        bypassReason.file_excluded = true
        bypassReasonDetails = 'This file is excluded from caching'
      }

      // 6. BYPASS cache if any custom cookie mentioned by the user in the plugin settings is present in the request
      // Check only for HTML type requests
      if (
        !bypassCache &&
        cookieHeader?.length > 0 &&
        response?.headers?.get('content-type')?.includes('text/html') &&
        response?.headers?.has('x-wp-cf-super-cache-cookies-bypass')
      ) {
        // Make sure the feature is enabled first
        if (response?.headers?.get('x-wp-cf-super-cache-cookies-bypass') !== 'swfpc-feature-not-enabled') {

          // Get the list of cookie names entered by the user in the plugin settings
          let cookies_blacklist = response?.headers?.get('x-wp-cf-super-cache-cookies-bypass')

          if (cookies_blacklist?.length > 0) {

            // Split the received cookie list with | separated and make an Array
            cookies_blacklist = cookies_blacklist.split('|')

            if (are_blacklisted_cookies(cookieHeader, cookies_blacklist)) {
              bypassCache = true
              bypassReason.cookie = true
              bypassReasonDetails = 'User provided excluded cookies present in request'
            }
          }
        }
      }

      //-----------------------------------------------------
      // Check if the request needs to be BYPASSed or Cached
      //-----------------------------------------------------
      if (!bypassCache) { // bypassCache is still FALSE. Cache the item in the CF Edge

        // Check if the response status code is not 206 or request method is not HEAD to cache using cache.put(), 
        // as any request with status code === 206 or req.method HEAD cache.put() will not work. 
        // More info: https://developers.cloudflare.com/workers/runtime-apis/cache#put
        if (response.status !== 206 || request?.method !== 'HEAD') {

          // If the response header has x-wp-cf-super-cache-active overwrite the cache-control header provided by the server value with x-wp-cf-super-cache-active value just to be safe
          if (response.headers?.has('x-wp-cf-super-cache-active')) {
            response.headers?.set('Cache-Control', response.headers?.get('x-wp-cf-super-cache-cache-control'))
          }

          // Set the worker status as miss and put the item in CF cache
          response.headers?.set('x-wp-cf-super-cache-worker-status', 'miss')

          // Add page in cache using cache.put()
          try {
            event.waitUntil( cache.put( cacheKey, response.clone() ) )
          } catch (err) {
            return new Response( 
              `Cache Put Error: ${err.message}`,
              { status: 500, statusText: `Cache Put Error: ${err.message}` } 
            )
          }

        } else {

          // Try to fetch this request again with cacheEverything set to TRUE as that is the only way to cache it
          // More info: https://developers.cloudflare.com/workers/runtime-apis/request#requestinitcfproperties
          try {
            response = await fetch(request, { cf: { cacheEverything: true } })
          } catch (err) {
            return new Response( 
              `Error: ${err.message}`,
              { status: 500, statusText: "Unable to fetch content from the origin server with cacheEverything flag" } 
            )
          }

          response = new Response(response.body, response)

          // Check if the response has any unusual origin server response code & if so then return the response
          if( has_unusual_origin_server_response_code(response) ) {
            return response
          }

          // Set the worker status as miss and put the item in CF cache
          response.headers?.set('x-wp-cf-super-cache-worker-status', 'miss')

        }
      } else { // bypassCache -> TRUE || Bypass the Request

        // BYPASS the request and add our custom headers
        add_bypass_custom_headers(response, bypassReasonDetails)
      }

    }

  } else { // bypassCache -> TRUE

    // Fetch the request from the origin server and send it by adding our custom bypass headers
    let bypassedResponse
    try {
      bypassedResponse = await fetch(request)
    } catch (err) {
      return new Response( 
        `Error: ${err.message}`,
        { status: 500, statusText: "Unable to fetch the bypassed content from the origin server" } 
      )
    }

    response = new Response(bypassedResponse?.body, bypassedResponse)

    // Check if the response has any unusual origin server response code & if so then return the response
    if( has_unusual_origin_server_response_code(response) ) {
      return response
    }

    // BYPASS the request and add our custom headers
    add_bypass_custom_headers(response, bypassReasonDetails)
  }

  return response
}

/**
 * Adding event lister to the fetch event to catch the requests and manage them accordingly
 * @param {Object} event 
 */
addEventListener('fetch', event => {
  try {
    return event.respondWith(handleRequest(event))
  } catch (err) {
    return event.respondWith( 
      new Response( 
        `Error thrown: ${err.message}`,
        { status: 500, statusText: `Error thrown: ${err.message}` } 
      ) 
    )
  }
})