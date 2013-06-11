<?php

class emailWrapper extends systemTemplate
{
	public function getTemplate( $content, $unsubscribe, $settings = array() )
	{
		return <<<EOF
<!DOCTYPE html> 
  <html lang="en"> 
  <head> 
    <meta charset="{$settings['gb_char_set']}" /> 
    <title><#subject#></title> 
    <style type="text/css"> 
      body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,textarea,p,blockquote,th,td { margin:0; padding:0; }  
      fieldset,img { border:0; } 
      address,caption,cite,code,dfn,th,var { font-style:normal; font-weight:normal; } 
      ol,ul { list-style:none; } 
      caption,th { text-align:left; } 
      h1,h2,h3,h4,h5,h6 { font-size:100%;  font-weight:normal; } 
      q:before,q:after { content:''; } 
      abbr,acronym { border:0; } 
      address{ display: inline; } 
       
      body { 
        font: normal 13px helvetica, arial, sans-serif; 
        position: relative; 
        background: #EBF0F3; 
        padding: 18px;  
      } 
       
      h3, strong { font-weight: bold; } 
      em { font-style: italic; } 
      img, .input_check, .input_radio { vertical-align: middle; } 
      legend { display: none; } 
     
      a { 
         
        text-decoration: none; 
      } 
       
      a:hover { color: #328586; } 
       
      div.outer { margin: 0 auto; border: 1px solid #CAD3DE; border-radius: 4px;moz-border-radius: 4px;webkit-border-radius: 4px; background: #fff; padding-bottom:6px } 
   
      h1.main { 
        font-family: "Lucida Grande", "Lucida Sans Unicode", "Helvetica"; 
        font-size:24px; 
        padding-bottom: 2px; 
        background-color: #D8DDE8; 
        padding: 8px 15px 2px 15px; 
        border-bottom: 1px solid #CAD3DE;   
      } 
         
      .content { 
        font-size: 12px !important; 
        color: #333 !important; 
        line-height: 120% !important; 
        padding: 15px 15px 0px 15px; 
      } 
       
      .content .callout { 
        background-color: #F7FBFC; 
        border: 1px solid #EBF0F3; border-radius: 4px;moz-border-radius: 4px;webkit-border-radius: 4px; 
        padding: 8px; 
        margin: 10px 0px 5px 0px; 
      } 
       
       .content .ipsBlockquote {  
        font-style: italic; 
        background-color: #efefef; 
        border: 1px solid #EBF0F3; border-radius: 4px;moz-border-radius: 4px;webkit-border-radius: 4px; 
        padding: 8px; 
        margin: 10px 0px 5px 0px; 
      }
      
      /* deprecated, leaving for backwards compatibility */
      .content .eQuote {  
        font-style: italic; 
        background-color: #efefef; 
        border: 1px solid #EBF0F3; border-radius: 4px;moz-border-radius: 4px;webkit-border-radius: 4px; 
        padding: 8px; 
        margin: 10px 0px 5px 0px; 
      } 
       
      .content .callout hr, .content .unsub hr { display: none; } 
       
      .content .unsub { color: #555; font-size: 11px; border-top: 1px solid #CAD3DE; padding-top:6px } 
       
      .footer { color: #444; font-size: 11px; padding:8px; text-align: center } 
       
      hr { display: block; 
         position: relative; 
         padding: 0; 
         margin: 8px auto; 
         width: 100%; 
         clear: both; 
         border: none; 
         border-top: 1px solid #CAD3DE; 
         border-bottom: 1px solid #FFF; 
         font-size: 1px; 
         line-height: 0; 
         overflow: visible; } 
          
      table.ipb_table { 
        line-height: 1.3; 
        border-collapse: collapse; 
      } 
        table.ipb_table td { 
          padding: 10px; 
          border-bottom: 1px solid #f3f3f3; 
        } 
           
          table.ipb_table tr.unread h4 { font-weight: bold; } 
          table.ipb_table tr.highlighted td { border-bottom: 0; } 
         
        table.ipb_table th { 
          font-size: 11px; 
          font-weight: bold; 
          padding: 8px 6px; 
        } 
         
      .last_post { margin-left: 45px; } 
       
      table.ipb_table h4, 
      table.ipb_table .topic_title { 
        font-size: 14px; 
        display: inline-block; 
      } 
       
      table.ipb_table  .unread .topic_title { font-weight: bold; } 
      table.ipb_table .ipsModMenu { visibility: hidden; } 
      table.ipb_table tr:hover .ipsModMenu, table.ipb_table tr .ipsModMenu.menu_active { visibility: visible; } 
 
    </style> 
 
  </head> 
  <body> 
  <div class='outer'> 
    <h1 class='main'>{$settings['board_name']}</h1> 
    <div class='content'> 
      {$content} 
      <div class='footer'> 
        <a href='{$settings['board_url']}'>{$settings['board_name']}</a>{$unsubscribe} 
      </div> 
    </div> 
  </div> 
  </body> 
</html>

EOF;

	}
}
