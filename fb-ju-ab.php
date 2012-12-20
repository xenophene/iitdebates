<?php
  require_once 'includes/config.php';
  include 'includes/aux_functions.php';
  
  $user = $fb->getUser();
  $signed_in = false;
  if ($user) {
    try {
      $profile = $fb->api('/me', 'GET');
      $signed_in = true;
    } catch (FacebookApiException $e) {}
  }
?>
<html>
  <head>
    <title>IIT Debates</title>
    <link rel="stylesheet" href="includes/assets/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="includes/assets/css/style.css"/>
    <link rel="icon" href="includes/assets/ico/favicon.ico"/>
    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-29694544-1']);
      _gaq.push(['_setDomainName', 'ramante.in']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
  </head>
  <body>
    <div id="header">
      <span class="logo"><a href="home.php">IIT Debates</a></span>
      <span class="options">
        <ul>
        <?php if ($signed_in): ?>
          <li class="log-out-link"><a href="home.php">Home</a></li>
          <li class="log-out-link" id="log-out-btn"><a href="logout.php">Log Out</a></li>
        <?php else: ?>
          <li class="log-out-link">
            <a href="<?php echo $fb->getLoginUrl($params);?>">Sign In</a>
          </li>
        <?php endif; ?>
        </ul>
      </span>
    </div>
      
    <div class="tabbable well">
      
      <ul class="nav nav-tabs">
        <li class="active"><a href="#join-us" data-toggle="tab">Join Us</a></li>
        <li><a href="#about" data-toggle="tab">About</a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="join-us">
          <h2>Join Us</h2>
          <p>
            We need help! Seriously, we have too many ideas and too few fingers to code all of them.
            We are a small team of people with a passion for building <em>something</em> for the web.
            If you like where we are going with all of this, and would like to contribute on the technical
            aspects, for product design or for content development/moderation, please drop us your mail and
            we will contact you. Please describe a little about your background and what you see IITD becoming.
          </p>
          <p>Send us a mail at iitdebates@gmail.com, and share your passion with us.</p>
        </div>
        
        <div class="tab-pane" id="about">
              <h2>About</h2>
              <p>
                This project is an attempt to resolve issues. To resolve differences. To accept each other's perspectives
                and to make the world a happier and more peaceful place.
              </p>
              <p>
                OK, these are big goals. But our attempt is through engaging people, both friends and
                outside of your social graph, in a friendly dialogue that one calls <strong>debating</strong>. This is a tool to facilitate real-time discussion among people about issues relevant to them, so the world can align its thinking and reach conclusions faster. It might also be a place where people discuss ideas,
                innovate, iterate and make the world a more sustainable environment.
              </p>
                <p>
                  IIT Debates is motivated by the fact that there are so many of our friends online, and we
                  <em>don't</em> know how to interact with them. There is a unique way by which we are connected to
                  each of our friends, and unfortunately we can't figure that out on generic social networks.
                </p>
                <p>
                  The social web is just about having your friends online. Its about how you
                  leverage those friends, how they add to your experience. Now that you
                  have a billion people online, the next challenge is how you keep them <em>engaged</em>. We among
                  many others believe that these people now need tools to engage and interact with each other in
                  novel ways and that is precisely what we are doing here. By Debating. We are still in <em>Alpha</em>
                  and would love any sort of feedback at this point. Don't be too pessimistic or critical though!
                  We would love to involve as many people as possible.
                </p>
                <p>
                  IIT Debates has been made possible principally by iterating over <a href="http://twitter.github.com/bootstrap/" target="_blank">Bootstrap</a> by Twitter among many other libraries and is an initiative by a couple of folks at IIT Delhi. You can reach us through the Contact Us Form.
                </p>
        </div>
      </div>
    </div>
  <script src="includes/assets/js/jquery-1.7.2.min.js"></script>
  <script src="includes/assets/js/bootstrap.min.js"></script>
  </body>
</html>