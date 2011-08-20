<?php
/**
* @version      $Id: dcpbookmarks.php 15 2011-05-04 15:50:20 dcpict $
* @author       DcP ICT
* @copyright    Copyright (C) 2011 DcP ICT. All rights reserved.
* @license      GNU/GPL
* Deze plugin is ontwikkeld door DcP ICT met de structuur van Josh in het achterhoofd.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

define('DCP_URL', JURI::base()."plugins/content/dcpbookmarks/dcpbookmarks");
define('DCP_VER', '1.1.0');

class plgContentDcpBookmarks extends JPlugin {

    function plgContentDcpBookmarks(&$subject, $config) {
        parent::__construct( $subject, $config );
    }

	function onContentBeforeDisplay($context, &$row, &$params, $page=0) {
		return $this->onPrepareContent($row, $params, $page);
	}

    function onPrepareContent( &$article, &$params, $limitstart='') {
        JPlugin::loadLanguage( 'plg_content_dcpbookmarks', JPATH_ADMINISTRATOR);

        $doc =& JFactory::getDocument();

        if($this->params->get(add_css) == 1) {
            $doc->addStyleSheet(DCP_URL."/style.css?ver=".DCP_VER);
        }

        if(!$this->checkCategories($article)) return;

        if($this->params->get('manual') != 1 && !$this->searchAntiBookmarkTag($article)) {
            $article->text = $this->deleteBookmarkTag($article->text);
            if($this->params->get('article') == 1 && JRequest::getVar('view') == 'article') {
                $article->text = $article->text.$this->fetchDcpHTML($article);
            } elseif($this->params->get('frontpage') == 1 && JRequest::getVar('view') == 'frontpage') {
                $article->text = $article->text.$this->fetchDcpHTML($article);
            } elseif($this->params->get('blog') == 1 && JRequest::getVar('layout') == 'blog') {
                $article->text = $article->text.$this->fetchDcpHTML($article);
            } else {
                return;
            }
        } else {
            $article->text = $this->searchBookmarkTag($article->text);
        }

        return;
    }

    function checkCategories(&$article) {
        if($this->params->get('include_cat') == "") return true;

        $include_array = explode(",", $this->params->get('include_cat'));

        if(in_array($article->catid, $include_array)) {
            return true;
        } else {
            return false;
        }

    }

    function searchBookmarkTag($text) {
        $searchtag = "{dcpbookmark}";

        if(substr_count($text, $searchtag) >= 1) {
            $text = $text.$this->fetchDcpHTML($article);
        } else {
            $text = $text;
        }

        $text = $this->deleteBookmarkTag($text);
        return $text;
    }

    function searchAntiBookmarkTag(&$article) {
        $searchtag = "{nodcpbookmark}";
        if(substr_count($article->text, $searchtag) >= 1) {
            $article->text = $this->deleteAntiBookmarkTag($article->text);
            return true;
        }

        return false;
    }

    function fetchBackground() {
        if($this->params->get('bgimg') == 'caring') {
            return " dcp-bookmarks-bg-caring";
        } elseif($this->params->get('bgimg') == 'sexy') {
            return " dcp-bookmarks-bg-dcp";
        } elseif($this->params->get('bgimg') == 'wealth') {
            return " dcp-bookmarks-bg-wealth";
        } elseif($this->params->get('bgimg') == 'care-old') {
            return " dcp-bookmarks-bg-caring-old";
        } elseif($this->params->get('bgimg') == 'love') {
            return " dcp-bookmarks-bg-love";
        } else {
            return;
        }
    }

    function fetchDcpHTML(&$article) {

        $echo_bookmarks = false;

        $j_config =& JFactory::getConfig();

        if($this->params->get('article') == 1 && JRequest::getVar('view') == 'article') {
            $url =& JFactory::getURI();
            $perms = $url->toString();
            $perms = str_replace( '&amp;', '&', $perms );
            $echo_bookmarks = true;
        } else {
            $user =& JFactory::getUser();
            if ($article->access <= $user->get('aid', 0)) {
                jimport('joomla.application.component.helper');
                $perms = JRoute::_($this->constructFrontpageUrl($article), false, -1);
                $echo_bookmarks = true;
            } else {
                $echo_bookmarks = false;
            }
        }

        if($j_config->getValue('config.sef') != 1) $perms = urlencode($perms);

        $title = urlencode($article->title);
        $title = str_replace('%3A',':',$title);
		$title = str_replace('+','%20',$title);
        $title = str_replace('%3F','?',$title);
        $title = str_replace('%C3%B9','ù',$title);
        $title = str_replace('%C3%A0','à',$title);
        $title = str_replace('%C3%A8','è',$title);
        $title = str_replace('%C3%AC','ì',$title);
        $title = str_replace('%C3%B2','ò',$title);
		
		$twit_title = urlencode ($article->title);
		$twit_title = str_replace('%3A',':',$twit_title);
        $twit_title = str_replace('%3F','?',$twit_title);
        $twit_title = str_replace('%C3%B9','ù',$twit_title);
        $twit_title = str_replace('%C3%A0','à',$twit_title);
        $twit_title = str_replace('%C3%A8','è',$twit_title);
        $twit_title = str_replace('%C3%AC','ì',$twit_title);
        $twit_title = str_replace('%C3%B2','ò',$twit_title);
		
        $short_title = substr($title, 0, 60)."...";

        $mail_subject = urlencode(substr($title, 0, 60)."...");
        $mail_subject = str_replace('+','&nbsp;',$mail_subject);
        $mail_subject = str_replace("&#8217;","'",$mail_subject);

        $dcp_content = urlencode(strip_tags(substr($article->text, 0, 220)."[..]"));
        $dcp_content = str_replace('+','%20',$dcp_content);
        $dcp_content = str_replace("&#8217;","'",$dcp_content);


        $post_summary = stripslashes($dcp_content);

        $dcp_teaser = $dcp_content;
        $strip_teaser = stripslashes($dcp_teaser);

        $site_name = $title;

        if($this->params->get('twitter') == 1 && $this->params->get('twittid') != "") {
            $short_url = $this->getShortUrl($perms);
            $post_by = "RT+@".$this->params->get('twittid').":+";
        }

        if ($this->params->get('twittley') == 1 && $article->metakey != '') {
            $twittley_cat = $this->params->get('twittcat');
            $twittley_tags = $article->metakey;
        } elseif($this->params->get('twittley') == 1 && $this->params->get('twittcat') != '') {
            $twittley_cat = $this->params->get('twittcat');
            $twittley_tags = $this->params->get('defaulttags');
        }

        $style = 'style="'.$this->params->get('xtrastyle').'"';

        $socials = "\n".'<div class="dcp-bookmarks'.$add_class.''.$this->fetchBackground().'" '.$style.' id="dcp-bookmarks"><ul id="socials" class="socials">'.
        ($this->params->get('linkedin') == 1 ? $this->fetchHTMLSnippet("dcp-linkedin", 'http://www.linkedin.com/shareArticle?mini=true&amp;url='.$perms.'&amp;title='.$title.'&amp;summary='.$post_summary.'&amp;source='.$site_name, JText::_("Share this on Linkedin")) : '').
        
        ($this->params->get('google') == 1 ? $this->fetchHTMLSnippet("dcp-google", "http://www.google.com/bookmarks/mark?op=add&amp;bkmk=".$perms."title=".$title, JText::_("Add this to Google Bookmarks")) : '').
        
        ($this->params->get('hyves') == 1 ? $this->fetchHTMLSnippet("dcp-hyves", "http://www.hyves-share.nl/button/tip/?tipcategoryid=12&amp;rating=5&amp;".$perms.'&amp;title='.$title.'&amp;body='.$short_url, JText::_("Deel dit op Hyves")) : '').
        
        ($this->params->get('twitter') == 1 && $this->params->get('twittid') != "" && $short_url != "" ? $this->fetchHTMLSnippet("dcp-twitter", 'http://www.twitter.com/home?status='.$post_by.'+'.$twit_title.'+-+'.$short_url, JText::_("Tweet This!")) : '').
        
        ($this->params->get('technorati') == 1 ? $this->fetchHTMLSnippet("dcp-technorati", "http://technorati.com/faves?add=".$perms, JText::_("Share this on Technorati")) : '').
        
        ($this->params->get('stumpleupon') == 1 ? $this->fetchHTMLSnippet("dcp-stumbleupon", "http://www.stumbleupon.com/submit?url=".$perms."&amp;title=".$title, JText::_("Stumble upon something good? Share it on StumbleUpon")) : '').
        
        ($this->params->get('reddit') == 1 ? $this->fetchHTMLSnippet("dcp-reddit", "http://reddit.com/submit?url=".$perms."&amp;title=".$title, JText::_("Share this on Reddit")) : '').
        
        ($this->params->get('myspace') == 1 ? $this->fetchHTMLSnippet("dcp-myspace", "http://www.myspace.com/Modules/PostTo/Pages/?u=".$perms."&amp;t=".$title, JText::_("Post this to MySpace")) : '').
        
        ($this->params->get('mixx') == 1 ? $this->fetchHTMLSnippet("dcp-mixx", "http://www.mixx.com/submit?page_url=".$perms."&amp;title=".$title, JText::_("Share this on Mixx")) : '').
        
        ($this->params->get('digg') == 1 ? $this->fetchHTMLSnippet("dcp-digg", "http://digg.com/submit?phase=2&amp;url=".$perms."&amp;title=".$title, JText::_("Digg this!")) : '').
        
        ($this->params->get('delicious') == 1 ? $this->fetchHTMLSnippet("dcp-delicious", "http://del.icio.us/post?url=".$perms."&amp;title=".$title, JText::_("Share this on del.icio.us")) : '').

		($this->params->get('facebook') == 1 ? $this->fetchHTMLSnippet("dcp-facebook", "http://www.facebook.com/share.php?u=".$perms."&amp;t=".$title, JText::_("Share this on Facebook")) : '').
        
        ($this->params->get('diigo') == 1 ? $this->fetchHTMLSnippet("dcp-diigo", "http://www.diigo.com/post?url=".$perms."&amp;title=".$title."&amp;desc=".$dcp_teaser, JText::_("Post this on Diigo")) : '').

        ($this->params->get('twittley') == 1 ? $this->fetchHTMLSnippet("dcp-twittley", "http://twittley.com/submit/?title=".$title."&amp;url=".$perms."&amp;desc=".$post_summary."&amp;pcat=".$twittley_cat."&amp;tags=".$twittley_tags, JText::_("Submit this to Twittley")) : '').

        '</ul></div>';

        if($echo_bookmarks === true) {
            return $socials;
        } else {
            return;
        }

    }

    function fetchHTMLSnippet($class, $url, $title) {
        if($this->params->get('add_nofollow') == 1) {
            $relopt = "nofollow";
        } else {
            $relopt = "";
        }

        if($this->params->get('open_in_newwindow') == 1) {
            $tarwin = "_blank";
        } else {
            $tarwin = "_self";
        }

        return "\n".'<li class="'.$class.'"><a href="'.$url.'" target="'.$tarwin.'" rel="'.$relopt.'" title="'.$title.'"> </a></li>';
    }

    function constructFrontpageUrl(&$article) {

        $needles = array(
            'article'  => (int) $article->slug,
            'category' => (int) $article->catslug,
            'section'  => (int) $article->sectionid
        );

        $link = 'index.php?option=com_content&view=article&id='. $article->slug;

        if($article->catslug) {
            $link .= '&catid='.$article->catslug;
        }

        if($item = plgContentDcpBookmarks::_findItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }

        return $link;
    }

    function deleteBookmarkTag($text) {
        return str_replace('{dcpbookmark}', '', $text);
    }

    function deleteAntiBookmarkTag($text) {
        return str_replace('{nodcpbookmark}', '', $text);
    }

    function getShortUrl($url) {

        switch ($this->params->get('url_service')) {
            case 'tinyurl':
                $api_url = 'http://tinyurl.com/api-create.php?url='.$url;
                break;
            case 'is.gd':
                $api_url = 'http://is.gd/api.php?longurl='.$url;
                break;
            case 'rims':
                $api_url = 'http://ri.ms/api-create.php?url='.$url;
                break;
            case 'tinyarro':
                $api_url = 'http://tinyarro.ws/api-create.php?url='.$url;
                break;
        }

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,$api_url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    function _findItem($needles) {
        $component =& JComponentHelper::getComponent('com_content');

        $menus  = &JApplication::getMenu('site', array());
        $items  = $menus->getItems('componentid', $component->id);

        $match = null;

        foreach($needles as $needle => $id)
        {
            foreach($items as $item)
            {
                if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
                    $match = $item;
                    break;
                }
            }

            if(isset($match)) {
                break;
            }
        }

        return $match;
    }

}
?>