<?php

namespace mergado;

class News {

    private $registry;
    private $version;
    private $extension_fullname;
    private $is_db_compatible;
    private $db;

    private $rss = array(
      'cs' => 'https://feeds.mergado.com/open-cs-b4cfbda402714aa0aeef75ba666161fb.xml',
      'sk' => 'https://feeds.mergado.com/open-sk-5d2f58e7dbe2b77cee0bd52388180c4a.xml',
      'en' => 'https://feeds.mergado.com/open-en-e2386b8bdbbed2c614dc313b49bf8742.xml',
      'pl' => 'https://feeds.mergado.com/open-pl-d2e654241f8ec716f930e402ad86459d.xml'
    );

    const DB_NEWS_TABLE = 'mergado_marketing_pack_news';

    function __construct($registry, $extension_fullname, $version, $is_db_compatible) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->is_db_compatible = $is_db_compatible;
        $this->version = $version;
        $this->extension_fullname = $extension_fullname;
    }

    public function __get($name) {
      return $this->registry->get($name);
    }

    public function getNews($lang_code = 'cs', $limit = 5){

        $news = array();
        if(!$this->is_db_compatible) {
          return array(
            'status' => -3,
            'msg' => 'incompatible db version',
            'data' => $news
          );
        }

        $lang_code_data = explode("-", $lang_code);
        if(count($lang_code_data) > 1) {
          $lang_code = $lang_code_data[0];
        }

        $rss_url = isset($this->rss[$lang_code]) ? $this->rss[$lang_code] : '';
        $status = Helper::urlExists($rss_url);

        if (!$status) {
            return array(
                'status' => -1,
                'msg' => 'invalid/unaccessible url',
                'data' => $news
            );
        } else {


          $context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
          if($raw_data = file_get_contents($rss_url , false, $context)) {
            $xml = simplexml_load_string(html_entity_decode($raw_data));
            
            if($xml !== FALSE) {
              $this->db->query("TRUNCATE TABLE `" . DB_PREFIX  . SELF::DB_NEWS_TABLE. "` ");
              foreach($xml->channel->item as $i){ //insert news to db

                $result = $this->db->query("SELECT * FROM `" . DB_PREFIX  . SELF::DB_NEWS_TABLE. "` WHERE guid=" . $i->guid . " AND lang='" . $lang_code . "'");
                if(!$result->num_rows) {
                  $this->db->query("INSERT INTO `" . DB_PREFIX  . SELF::DB_NEWS_TABLE. "` (guid, lang, title, content, category, pubdate, url) VALUES (" . $i->guid . ",'{$lang_code}','" . $this->db->escape($i->title) . "','" . $this->db->escape($i->description) . "', '" . $this->db->escape($i->category) . "','" . date('Y-m-d H:i:s', strtotime($i->pubDate)) . "','{$i->link}')");
                } 

              }
            }
    
          } else {
            return array(
                'status' => -2,
                'msg' => 'invalid xml',
                'data' => $news
            );
          }
    
        }
    
        return  array(
                    'status' => 1,
                    'msg' => 'success',
                    'data' => $this->loadNews($lang_code, $limit)
                );
      }

      public function loadNews($lang_code, $limit) {

        $news = array();
        $iterator = [0,0];
  
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX  . SELF::DB_NEWS_TABLE. "` WHERE lang='" . $lang_code . "' ORDER BY guid DESC");
  
        foreach ($result->rows as $i) {
  
          if((String) $i['category'] == 'update') {
              $key = 'update';
              if($iterator[0] == $limit) { continue; } 
              $iterator[0]++;
          } else {
              $key = 'news';
              if($iterator[1] == $limit) { continue; }  
              $iterator[1]++;  
          }
  
          if(in_array($lang_code, array('cs', 'sk', 'pl'))) {
            $pubDate = date('d.m.Y H:i', strtotime($i['pubdate']));
          } else {
            $pubDate = date('Y-m-d H:i', strtotime($i['pubdate']));
          }
  
          $news[$key][] = array(
            'guid' => $i['guid'],
            'pubdate' => $pubDate,
            'title' => (string) $i['title'],
            'description' => (string) $i['content'],
            'category' => (string) $i['category'],
            'url' => (string) $i['url']
          );
        }
  
        return $news;
  
      }

}
