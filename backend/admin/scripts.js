// source: http://www.somacon.com/p143.php
function setMutationTo( value ) {
  var radioobj = document.forms['newRemarkForm'].elements['mutation'];
  if( !radioobj ) return;
  var radioLength = radioobj.length;
  if( !radioLength ) return;
  for( var i = 0; i < radioLength; i++ ) {
    radioobj[i].checked = false;
    if( radioobj[i].value == value.toString() ) {
      radioobj[i].checked = true;
    }
  }
}

function mark( state )
{
  var checkboxobj = document.forms['newRemarkForm'].elements;
  if( ! checkboxobj ) return;
  var checkboxLength = checkboxobj.length;
  if( !checkboxLength ) return;
  for( var i = 0; i < checkboxLength; i++ ) {
    checkboxobj[i].checked = state ? true : false;
  }

  // For onclick events
  return false;
}
