/**
  * Defines a debate on a user clicking start a new debate. This function
  * is the base function. The parameters which are requested:
  * Debate topic, Debate description, Debate themes, Context links/urls, Friends
  * who are challenged for or against this debate, the debate deadline
  */
function defineDebate() {
  var id = '#start-debate-form';
  $( "#debate-theme" ).autocomplete({
    minLength: 3,
    source: function( request, response ) {
      // delegate back to autocomplete, but extract the last term
      response( $.ui.autocomplete.filter(
	      themes, extractLast( request.term ) ) );
    },
    focus: function() {
      // prevent value inserted on focus
      return false;
    },
    select: function( event, ui ) {
      var terms = split( this.value );
      // remove the current input
      terms.pop();
      // add the selected item
      terms.push( ui.item.value );
      // add placeholder to get the comma-and-space at the end
      terms.push( "" );
      this.value = terms.join( ", " );
      return false;
    },
    maxResults: 4
  });
  /* Here we query the user's fb friends for whom we also get the userids.
     We send the fb friends's userids. Incase a particular fb friend doesn't 
     exist in our db, we need to somehow intimate that person */
  if (friendNames == null) {
    $.ajax({
      url: 'includes/ajax_scripts.php',
      type: 'POST',
      data: {
        'fid' : 2
      },
      dataType: 'json',
      error: function (msg) {
        console.log(msg);
      },
      success: function(data) {
        var result = data;
        var names = Array();
        var ids = Array();
        for (var i = 0; i < result.data.length; i++) {
          names.push(result.data[i].name);
          ids.push(result.data[i].id);
        }
        $( "#participants").autocomplete({
	  minLength: 3,
	  source: function( request, response ) {
	    // delegate back to autocomplete, but extract the last term
	    response( $.ui.autocomplete.filter(
		    names, extractLast( request.term ) ) );
	  },
	  focus: function() {
		  // prevent value inserted on focus
		  return false;
	  },
	  select: function( event, ui ) {
		  var terms = split( this.value );
		  // remove the current input
		  terms.pop();
		  // add the selected item
		  terms.push( ui.item.value );
		  // add placeholder to get the comma-and-space at the end
		  terms.push( "" );
		  this.value = terms.join( ", " );
		  return false;
	  },
	  maxResults: 4
	});
        friendNames = names;
        friendIds = ids;
      }
    });
    // enable the participants file
    $('#participants').show();
    $('#cancel-debate').show();
  }
  else {
    $( "#participants" ).autocomplete({
	    minLength: 3,
	    source: function( request, response ) {
		    // delegate back to autocomplete, but extract the last term
		    response( $.ui.autocomplete.filter(
			    friendNames, extractLast( request.term ) ) );
	    },
	    focus: function() {
		    // prevent value inserted on focus
		    return false;
	    },
	    select: function( event, ui ) {
		    var terms = split( this.value );
		    // remove the current input
		    terms.pop();
		    // add the selected item
		    terms.push( ui.item.value );
		    // add placeholder to get the comma-and-space at the end
		    terms.push( "" );
		    this.value = terms.join( ", " );
		    return false;
	    },
	    maxResults: 4
    });
  }
  $(id).modal('show');
}

$('#start-debate-form form').submit(function() {
  var participants = $('#participants').val().split(',');
  var np = [];
  var indexes = [];
  for (var i = 0; i < participants.length; i++) {
    var s = $.trim(participants[i]);
    var j = $.inArray(s, friendNames);
    if (j != -1) {
      indexes[i] = friendIds[j];
      np[i] = s;
    }
  }
  $('#debate-desc').val($('#debate-desc').val().replace(/(^,)|(,$)/g, ""));
  $('#participants').val(np.join());
  $('#participant-ids').val(indexes.join());
  var debtopic = $('#debate-topic').val();
  var debdesc = $('#debate-desc').val();
  var debthemes = $('#debate-theme').val();
  var partids = $('#participant-ids').val();
  var partnames = $('#participants').val();
  var timelimit = $('input[name=time-limit]:checked').val();
  var privacy = $('input[name=privacy]:checked').val();
  var postToFb = $('#post-to-fb-input').val();
  // send these variables over to debate-create and wait! need to think of some authentication?!
  $.ajax({
    url: 'includes/debate-create.php',
    type: 'POST',
    data: {
      'debate-topic': debtopic,
      'debate-desc': debdesc,
      'debate-theme': debthemes,
      'participant-ids': partids,
      'participants':	partnames,
      'time-limit': timelimit,
      'privacy': privacy,
      'post-to-fb-input': postToFb,
      'uname': uname
    },
    success: function (data) {
      console.log(data);
      if (!data) console.log('something bad happened!');
      else window.location = 'debate.php?debid=' + data;
    },
    error: function(msg) {
      console.log(msg);
    }
  });
  $('#start-loading').toggle();
  return false;
});

/* subset of defineDebate for a TARGETTED debate*/
function defineChallengeDebate() {
  defineDebate();
  $('#participants').val($('tr td.name').html() + ', ');
}

/* Follow this user, toggling the state/css, to unfollow and follow */
function auxFollowUser(elmt, oldClassName, newClassName, fCode, htmlCode) {
  elmt.removeClass(oldClassName);
  elmt.addClass(newClassName);
  elmt.html(htmlCode);
  /* send follow AJAX request */
  $.ajax({
    url: 'includes/ajax_scripts.php',
    type: 'POST',
    data: {
      fid: 9,
      follower: myfbid,
      followee: uuid,
      follow: fCode
    },
	success: function (data) {
	  console.log(data);
	}
  });
}
function followUser () {
  if ($(this).hasClass('btn-primary')) {
    auxFollowUser($(this), 'btn-primary', 'btn-danger', 1, 'Unfollow');
  }
  else {
    auxFollowUser($(this), 'btn-danger', 'btn-primary', 0, 'Follow');
  }
}

function clearDebateForm() {
  $('#debate-topic').val('');
  $('#debate-desc').val('');
  $('#debate-theme').val('');
  $('#participants').val('');
}
/* modify the my debating interests using tag-it helper js */
function reShowAddButton(interests) {
  $('.interest-confirm').tooltip('hide');
  $('.interest-reject').tooltip('hide');
  $('.interest-confirm').unbind();
  $('.interest-reject').unbind();
  $('.interest-elements-p').html('<span class="add">+</span>');
  $('.interest-elements').html(interests);
  $('.interest-elements').effect("highlight", {}, 3000);
  $('span.add').click(modifyInterests);
  $('.add').tooltip({
    title: 'Modify/Add Debating interests'
  });
}
function modifyInterests() {
  var interests = $('.interest-elements').html();
  $(this).html('');
  $('.interest-elements').html('');
  $('.add').tooltip('hide');
  $('.add').unbind();
  $(this).append('<span title="Confirm" class="interest-confirm icon-ok" style="margin:4px 0 0 4px;padding:0 4px 0 4px;"></span>');
  $(this).append('<span title="Reject" class="interest-reject icon-remove" style="margin:4px 0;padding:0 4px 0 4px;"></span>');
  $('.interest-elements').prepend('<input type="text" style="margin:0;">');
  $('.interest-elements input').val(interests);
  $('.interest-confirm').tooltip({
    title: 'Accept'
  });
  $('.interest-reject').tooltip({
    title: 'Cancel'
  });
  $('.interest-confirm').click(function() {
    // take the text and enter in the db, also show the text
    var interests = $('.interest-elements input').val();
    $.ajax({
      url: 'change-interests.php',
      type: 'POST',
      data: {uid: uuid, interests: interests}
    });
    reShowAddButton(interests);
  });
  $('.interest-reject').click(function() {
    reShowAddButton(interests);
  });
}

/* delete Debate will take the debate and remove myself from the participant list */
function debateDelete() {
  $('.delete-debate').tooltip('hide');
  var debid = $(this).parent().parent().children('td.dname').attr('id');
  $.ajax({
    url: 'includes/ajax_scripts.php',
    type: 'POST',
    data: {
      fid: 6,
      debid: debid,
      user: myfbid
    }
  });
  $(this).parent().parent().fadeOut();
  $(this).parent().parent().remove();
  if (!$('.debate-table tbody').children().size()) { // remove the tbody
    $('.debate-table tbody').append('<tr id="nill"><td>You ' + "don't have any ongoing debates right now</td></tr>");
    $('.debate-table thead').html('');
  }
}

/* clear Overlay */
function clearOverlay() {
  $('.window').hide();
  $('#mask').hide();
}

/* show my connections */
function showConnections(evt) {
  var pids = evt.data.p1 == 1 ? followers : followees;
  var heading = evt.data.p1 == 1 ? 'Followers' : 'Followees';
  if (!pids.length) return;
  var n = pids.length;
  var code = '<ul>';
  for (var i = 0; i < n; i++) {
    code += '<li id="' + pids[i] + '"><a target="_blank" href="home.php?fbid=' +
	  pids[i] + '"><img id="' + pids[i] + '" src="https://graph.facebook.com/' +
	  pids[i] + '/picture"/></a></li>';
  }
  code += '</ul>';
  var id = '#overlay';
  renderOverlay(id, heading, code);
}

function popovers() {
  $('.debate-table').popover({
    title: 'The Debate Table',
    content: 'View performance on ongoing & past debates'
  });
  $('#interested-in').popover({
    title: 'Interested In',
    content: 'All the debating themes you are interested to debate in.'
  });
  $('#debating-points').popover({
    title: 'Debating Points',
    content: 'Debating Points accumulated over time by winning valuable debates. The points for a debate result from the popularity that the debate garners over time. When a debate gets over, the points it had get distributed among its participants. The more votes a comment got, the more points its author gets at the end.'
  });
  $('#debates-won').popover({
    title: 'Debates Won',
    content: 'Number of debates won over time.'
  });
  $('#modify-profile').popover({
    title: 'Modify Profile',
    content: 'Modify your profile to add debate themes and interests.',
    placement: 'bottom'
  });
  $('#start').popover({
    title: 'Start a new debate',
    content: 'Start a new debate by defining the topic giving description through relevant links & themes. Invite your friends to participate in the debate and set the time limit for the debate. Once the time limit expires, no participants will be able to add new comments.',
    placement: 'bottom'
  });
  $('#debate-topic').popover({
    content: 'Enter the debate topic'
  });
  $('#debate-desc').popover({
    content: 'Give some optional description to motivate the need to debate this topic and who all should care for the topic. Provide more context to the debate by providing external URLs giving it a defined direction.'
  });
  $('#debate-theme').popover({
    content: 'Enter one or more of the predefined categories that this debate falls under'
  });
  $('#participants').popover({
    content: 'Invite your friends who would be most interested to express their views on this topic'
  });
  $('#radio').popover({
    content: 'Set a time limit for this debate after which no participant will be able to make further comments.'
  });
  $('#radio2').popover({
    content: 'Any debater can participate & comment in a public debate. Private debates require invites from participants.'
  });
  $('#invite').popover({
    content: 'Invite this person to one my ongoing debates',
    placement: 'left'
  });
  $('#follow').popover({
    content: "Follow this person's debates and activity",
    placement: 'left'
  });
	$('#challenge').popover({
    content: "Challenge this person to a new debate",
    placement: 'left'
  });
  $('#post-to-fb').tooltip();
  $('.add').tooltip({
    title: 'Modify/Add Debating interests'
  });
  $('#friend-search').tooltip({
    title: 'Search Debaters on IIT Debates',
    placement: 'left'
  });
  $('.delete-debate').tooltip({
    title: 'Remove myself as a participant',
    placement: 'left'
  });
}

function postFb() {
  $(this).toggleClass('active');
  if ($('#post-to-fb-input').val() == '0') {
    $('#post-to-fb-input').val('1');
    $(this).html('This debate will be posted on Facebook');
  }
  else {
    $('#post-to-fb-input').val('0');
    $(this).html('Post this debate on to Facebook');
  }
}
function setEditable() {
	$('.editable').each(function () {
		var field_type
			, id;
		id = $(this).attr('name');
		
		if ($(this).hasClass('interest-elements')) {
			field_type = 'interests';
		}
		console.log('1');
		$(this).editInPlace({
			url: "includes/ajax_scripts.php",
			params: keyValueString({
				'fid': '12',
				'field_type': field_type,
				'id': id
			}),
			success : function(newEditorContentString){return newEditorContentString;},
			field_type: "text",
			saving_image: "./includes/assets/img/ajax-loader.gif",
			show_buttons: true
		});
	});
}
$(function() {
  clearDebateForm();
  $('#start').click(defineDebate);
  $('#start-debate-form input').keyup(function() {
    if ($('#debate-topic').val().length > 5 && $('#participants').val().length > 3)
      $('#start-debate').removeAttr('disabled');
  });
  $('#post-to-fb').click(postFb);
  $('#start-debate-form').on('hidden', clearDebateForm);
  $('#challenge').click(defineChallengeDebate);
  $('#follow').click(followUser);
  $('#radio').buttonset();
  $('#radio2').buttonset();
  $('span.add').click(modifyInterests);
  $('.delete-debate').click(debateDelete);
  $('#my-followers').click({p1: '1'}, showConnections);
  $('#my-followees').click({p1: '2'}, showConnections);
  popovers();
  searchSetup();
	setEditable();
});