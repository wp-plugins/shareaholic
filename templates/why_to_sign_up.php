<style type="text/css">
	body {
	font-family: sans-serif;
}

.signuppromo{
  border: 1px solid #1E8520;
	background: #45A147;
	border-radius: 6px;
	padding: 20px 20px;
	color: white;
	font-size: 14px;
	float: right;
	margin: 30px;
	min-width: 250px;
	box-shadow: 0px 1px 5px rgba(0,0,0,0.27);
}

.promoh1{
	font-size: 24px;
	line-height: 28px;
	margin: 10px 0 0 0;
	padding: 0;
	text-shadow: 0px 1px 0px rgba(0,0,0,0.4);
}

.promosub{
	color: black;
	margin: 10px 0 0 0;
	font-weight: bold;
}

.signuppromo ul{
margin: 24px 0px;
}

.signuppromo ul li{
margin: 10px 0px;
}


.signuppromo a,.signuppromo a:active {


        -moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
        -webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
        box-shadow:inset 0px 1px 0px 0px #ffffff;

        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ffffff), color-stop(1, #f6f6f6));
        background:-moz-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
        background:-webkit-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
        background:-o-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
        background:-ms-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
        background:linear-gradient(to bottom, #ffffff 5%, #f6f6f6 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f6f6f6',GradientType=0);

        background-color:#ffffff;

        -moz-border-radius:6px;
        -webkit-border-radius:6px;
        border-radius:6px;
       width: 50%;
        border:1px solid #dcdcdc;
        text-align: center;
        margin: 0 auto;
        display:block;
        color:#666666;
        font-size:15px;
        font-weight:bold;
        padding:16px 24px;
        text-decoration:none;

        text-shadow:0px 1px 0px #ffffff;

    }
    .signuppromo a:hover {

        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #f6f6f6), color-stop(1, #ffffff));
        background:-moz-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
        background:-webkit-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
        background:-o-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
        background:-ms-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
        background:linear-gradient(to bottom, #f6f6f6 5%, #ffffff 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f6f6f6', endColorstr='#ffffff',GradientType=0);

        background-color:#f6f6f6;
    }

}
</style>


<div class="signuppromo unit size1of5">
<p class="promoh1">Customize even more with a FREE Shareaholic account.</p>
<p class="promosub">Such as:</p>

<ul>
  <li>Customize tweets coming from your website.</li>
  <li>Choose your URL Shortener, or use your own.</li>
  <li>Choose from various themes and styles.</li>
  <li>Exclude pages from Recommendations engine.</li>
  <li>Plus tons of great features and customization options.</li>
</ul>
<button data-href='edit' id='general_settings' class="btn btn-large"><?php echo sprintf(__('Edit General Website Settings', 'shareaholic')); ?></button>
</div>