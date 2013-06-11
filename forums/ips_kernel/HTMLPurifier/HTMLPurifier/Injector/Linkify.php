<?php

/**
 * Injector that converts http, https and ftp text URLs to actual links.
 */
class HTMLPurifier_Injector_Linkify extends HTMLPurifier_Injector
{

    public $name = 'Linkify';
    public $needed = array('a' => array('href'));

    public function handleText(&$token) {
        if (!$this->allowsElement('a')) return;

        if (strpos($token->data, '://') === false) {
            // our really quick heuristic failed, abort
            // this may not work so well if we want to match things like
            // "google.com", but then again, most people don't
            return;
        }
//print $token->data;
        // there is/are URL(s). Let's split the string:
        // Note: this regex is extremely permissive
        //$bits = preg_split('#((?:https?|ftp)://[^\s\'"<>()]+)#S', $token->data, -1, PREG_SPLIT_DELIM_CAPTURE);
        /* MODIFIED April 26, 2013
            @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/links-being-corrupted-or-malformed-in-board-and-nexus-r41993
            Test case:
http://invisionpower.com,
http://invisionpower.com.
http://invisionpower.com
https://invisionpower.com
https://blah.gov/blah-blah.as
http://en.wikipedia.org/wiki/Chi_(mythology)
(http://google.com)
             */
        preg_match_all( "#(.*?)(\()?((?:http|ftp|https):\/\/[\w\-_]+(?:\.[\w\-_]+)?(?:[\w\-\.,\(\)@?^=%&amp;:/~\+\#]*[\w\-\@?^=%&amp;/~\+\#]))(.*?)$#ims", $token->data, $matches );
        //print_r($matches);exit;

        $token = array();

        // $i = index
        // $c = count
        // $l = is link
        /*for ($i = 0, $c = count($bits), $l = false; $i < $c; $i++, $l = !$l) {
            if (!$l) {
                if ($bits[$i] === '') continue;
                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
            } else {
                $token[] = new HTMLPurifier_Token_Start('a', array('href' => $bits[$i]));
                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
                $token[] = new HTMLPurifier_Token_End('a');
            }
        }*/

        if( is_array($matches) AND count($matches) )
        {
            foreach( $matches[0] as $k => $match )
            {
                if( !$matches[3][$k] )
                {
                    $token[]   = new HTMLPurifier_Token_Text($token->data);
                }
                else
                {
                    if( $matches[1][$k] )
                    {
                        $token[] = new HTMLPurifier_Token_Text($matches[1][$k]);
                    }

                    if( $matches[2][$k] )
                    {
                        $token[] = new HTMLPurifier_Token_Text($matches[2][$k]);
                    }

                    if( !$matches[2][$k] AND $matches[4][$k] == ')' )
                    {
                        $matches[3][$k] .= ')';
                        unset($matches[4][$k]);
                    }

                    $token[] = new HTMLPurifier_Token_Start('a', array('href' => $matches[3][$k]));
                    $token[] = new HTMLPurifier_Token_Text($matches[3][$k]);
                    $token[] = new HTMLPurifier_Token_End('a');

                    if( $matches[4][$k] )
                    {
                        $token[] = new HTMLPurifier_Token_Text($matches[4][$k]);
                    }
                }
            }
        }
//print_r($token);exit;
    }

}

// vim: et sw=4 sts=4
