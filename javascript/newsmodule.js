
$.fn.tagcloud.defaults = {
  size: {start: 0.5, end: 5, unit: 'em'},
  color: {start: '#B00', end: '#8EA376'}
};

$(function(){
	$('.tagCloud a').tagcloud();
});