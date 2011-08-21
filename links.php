#!/usr/bin/php
#
# Usage:
# To extract links from a live site, simply supply the URL as a parameter
# ./links.php http://www.boost.org
#
# To extract links from a file, specify /f switch followed by a filename
# and then by the URL (for relative URL building)
# ./links.php /f boost.html http://www.boost.org
#
<?PHP
  require './http_build_url.php';

  # Original PHP code by Chirp Internet: www.chirp.com.au
  # Please acknowledge use of this code by including this header.

  function robots_allowed($url, $useragent=false)
  {
    # parse url to retrieve host and path
    $parsed = parse_url($url);

    $agents = array(preg_quote('*'));
    if($useragent) $agents[] = preg_quote($useragent);
    $agents = implode('|', $agents);

    # location of robots.txt file
    $robotstxt = @file("http://{$parsed['host']}/robots.txt");
    if(!$robotstxt) return true;

    $rules = array();
    $ruleapplies = false;
    foreach($robotstxt as $line) {
      # skip blank lines
      if(!$line = trim($line)) continue;

      # following rules only apply if User-agent matches $useragent or '*'
      if(preg_match('/User-agent: (.*)/i', $line, $match)) {
        $ruleapplies = preg_match("/($agents)/i", $match[1]);
      }
      if($ruleapplies && preg_match('/Disallow:(.*)/i', $line, $regs)) {
        # an empty rule implies full access - no further tests required
        if(!$regs[1]) return true;
        # add rules that apply to array for testing
        $rules[] = preg_quote(trim($regs[1]), '/');
      }
    }

    foreach($rules as $rule) {
      # check if page is disallowed to us
      if(isset($parsed['path'])  &&  preg_match("/^$rule/", $parsed['path'])) return false;
    }

    # page is not disallowed
    return true;
  }

    // http://www.web-max.ca/PHP/misc_24.php

    //  $url = "http://www.goat.com/money/dave.html";
    //  $rel = "../images/cheese.jpg";
    //  $com = InternetCombineURL($url,$rel);
    //  Returns http://www.goat.com/images/cheese.jpg
    function InternetCombineUrl($absolute, $relative) {
        $p = parse_url($relative);
        if(isset($p["scheme"]) && $p["scheme"])return $relative;
        
        extract(parse_url($absolute));
        
        if (isset($path))
	    $path = dirname($path);
	else
	    $path = ''; 
    
        if($relative{0} == '/') {
            $cparts = array_filter(explode("/", $relative));
        }
        else {
            $aparts = array_filter(explode("/", $path));
            $rparts = array_filter(explode("/", $relative));
            $cparts = array_merge($aparts, $rparts);
            foreach($cparts as $i => $part) {
                if($part == '.') {
                    $cparts[$i] = null;
                }
                if($part == '..') {
                    $cparts[$i - 1] = null;
                    $cparts[$i] = null;
                }
            }
            $cparts = array_filter($cparts);
        }
        $path = implode("/", $cparts);
        $url = "";
        if(isset($scheme) && $scheme) {
            $url = "$scheme://";
        }
        if(isset($user) && $user) {
            $url .= "$user";
            if($pass) {
                $url .= ":$pass";
            }
            $url .= "@";
        }
        if(isset($host) && $host) {
            $url .= "$host/";
        }
        $url .= $path;
        return $url;
    }


  function construct_url($str)
  {
    $url = parse_url($str);
    if (!isset($url['scheme']))
      $url['scheme'] = 'http';
    return http_build_url($str,$url);
  }

  // Original PHP code by Chirp Internet: www.chirp.com.au
  // Please acknowledge use of this code by including this header.
  // http://www.the-art-of-web.com/php/parse-links/

  ini_set('user_agent', 'sunaweb (http://www.cdmh.co.uk)');

  $url = "http://craighenderson.co.uk";

  if ($argc > 1)
  {
      if ($argv[1] == '/f'  ||  $argv[1] == '-f')
      {
        $html = $argv[2];
	$url = construct_url($argv[3]);
      }
      else
      {
        $html = construct_url($argv[1]);
        $url = $html;

        if (!robots_allowed($url, "sunaweb"))
          die('Access denied by robots.txt');
      }
  }

  $input = @file_get_contents($html) or die("Could not access file: $html");
  $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
  if (preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER))
  {
    foreach($matches as $match)
    {
      $link = $match[2];
      if (substr($link,0,1) == "'")
        $link = substr($link,1,strlen($link)-2);

      if (substr($match[2],0,5) != "'http")
        $link = InternetCombineURL($url,$link);

      echo construct_url($link) . "\n"; // link address
    }
  }
?>
