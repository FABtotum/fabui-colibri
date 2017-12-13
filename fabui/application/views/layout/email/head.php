<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<meta name="viewport" content="width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<style>
    body {
	   -webkit-font-smoothing: antialiased;
	   -webkit-text-size-adjust: none;
	   width: 100% !important;
	   height: 100%
    }
    * {
	   margin: 0;
	   padding: 0;
    }

    * {
	   font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif
    }
    
    h1, h2, h3, h4, h5, h6 {
	   font-family: "HelveticaNeue-Light", "Helvetica Neue Light",
		"Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
	   line-height: 1.1;
	   margin-bottom: 15px;
	   color: #000
    }
    
    h3 {
	   font-weight: 500;
	   font-size: 27px
    }

    table.head-wrap {
    	width: 100%;
    	background-color: #2196F3;
    }
    
    .header.container table td.logo {
	   padding: 15px;
    }
    
    .header.container table td.label {
	   padding: 15px;
	   padding-left: 0;
    }
    
    .header.container table td.logo {
	   padding: 15px;
    }

    .header.container table td.label {
    	padding: 15px;
    	padding-left: 0;
    }
    
    .footer-wrap .container td.content p {
    	border-top: 1px solid #d7d7d7;
    	padding-top: 15px;
    }
    
    .footer-wrap .container td.content p {
    	font-size: 10px;
    	font-weight: bold;
    }
    
    .content {
    	padding: 15px;
    	max-width: 600px;
    	margin: 0 auto;
    	display: block
    }

    .content table {
    	width: 100%
    }
    .container {
    	display: block !important;
    	max-width: 600px !important;
    	margin: 0 auto !important;
    	clear: both !important;
    }
    
    p, ul {
        margin-bottom: 10px;
        font-weight: normal;
        font-size: 14px;
        line-height: 1.6;
    }
    
    table.body-wrap {
	   width: 100%;
    }
    
    table.footer-wrap {
    	width: 100%;
    	clear: both !important;
    }
    
    .footer-wrap .container td.content p {
    	border-top: 1px solid #d7d7d7;
    	padding-top: 15px;
    }
    
    .footer-wrap .container td.content p {
    	font-size: 10px;
    	font-weight: bold;
    }
    
    .note {
        margin-top: 6px;
        padding: 0 1px;
        font-size: 11px;
        line-height: 15px;
        color: #999;
    }
    
    .callout {
        padding:15px;
        background-color:#ecf8ff;
        margin-bottom:15px;
     }
     
    .lead {
	   font-size: 17px
    }
    
    dt {
    	font-weight: 700;
    }
    
    
    @media only screen and (min-width:600px) {
    .dl-horizontal dt {
    		float: left;
    		width: 160px;
    		clear: left;
    		text-align: right;
    		overflow: hidden;
    		text-overflow: ellipsis;
    		white-space: nowrap;
    	}
    	.dl-horizontal dd {
    		margin-left: 180px;
    	}
    } 
</style>
<?php echo $this->cssInline; ?>