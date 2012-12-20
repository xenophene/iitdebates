// global variables useful across files
var names = Array();
var ids = Array();
var friendIds = null;
var friendNames = null;
var uids = Array();
var searchtypes = Array();
var themes = Array('IIT Mess', 'IIT Politics', 'IIT Academics', 'IIT Hostels', 'IIT Cultural Events', 'IIT Sports Events', 'Nation & Economy');
/* Library of common auxiliary functions that will be used by all the main
  js files on the respective pages */
function split( val ) {
  return val.split( /,\s*/ );
}
function extractLast( term ) {
  return split( term ).pop();
}
// add the border to the taller of the left-right divs
function add_divider() {
  var h1 = $('#content .leftcol').css('height').split('px')[0];
  var h2 = $('#content .rightcol').css('height').split('px')[0];
  if (parseInt(h1) >= parseInt(h2)) {
    $('#content .leftcol').css('border-right', '1px solid #eee');
  } else {
    $('#content .rightcol').css('border-left', '1px solid #eee');
  }
}
/* Set up the user search functionality by querying through AJAX the user base */
function searchSetup() {
  $.ajax({
    url: 'includes/ajax_scripts.php',
    type: 'POST',
    data: {
      'fid': 3
    },
    dataType: 'json',
    error: function (msg) {
      console.log(msg);
    },
    success: function(data) {
      for (var i = 0; i < data.length; i++) {
        var x = data[i];
        names.push($.trim(x.name));
        uids.push(x.uid);
        searchtypes.push(x.ltype);
      }
      $('#friend-search').typeahead({
        source: names,
        items: 5
      });
    }
  });
  $('#friend-search').keypress(function(evt) {
    if (evt.which != 13) return true;
    else {
      var sname = $(this).val();
      console.log(sname);
      var i = $.inArray(sname, names);
      if (i != -1) {
        if (searchtypes[i] == 'u') window.location = 'home.php?uid=' + uids[i];
        else  window.location = 'debate.php?debid=' + uids[i];
      }
      return false;
    }
  });
  $('.icon-search').click(function() {
    var sname = $(this).parent().children('input').val();
    var i = $.inArray(sname, names);
    if (i != -1) window.location = 'home.php?uid=' + uids[i];
    else $(this).parent().children('input').val('');
  });
}

function renderOverlay(id, heading, code) {
  $(id + ' .modal-header h1').html(heading);
  if (code == '<ul></ul>') $(id + ' .modal-body').html('<p>No users in this activity</p>');
  else $(id + ' .modal-body').html(code);
  
  $(id + ' li a img').each(function () {
    nameFromId($(this), $(this).parent().parent().attr('id'));
  });
  $(id).modal('show');
}

function nameFromId(elmt, fbid) {
  $.ajax({
    url: 'https://graph.facebook.com/' + fbid,
    method: 'GET',
    datatype: 'json',
    success: function (data) {
      console.log(data.name);
    },
    error: function (data) {
      var n = $.parseJSON(data.responseText).name;
      elmt.attr('title', n);
      elmt.tooltip();
    }
  });
}

$(function () {
  add_divider();  
});
