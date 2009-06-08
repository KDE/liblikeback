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
