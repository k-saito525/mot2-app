$(function(){
	'use strict';
	
	function infoAccordionToggle() {
    const $btn = $('.l-header__info-btn');
		const $accordion = $('.l-header__info');

    $btn.on('click', function() {
      $accordion.slideToggle(200);
    })
	}

  function topicAccordionToggle() {
    const $btn = $('.js-accordion-topic');

    $btn.on('click', function() {
      $(this).next('.p-sub__inner').slideToggle(200);
      return false;
    })
  }

	infoAccordionToggle();
	topicAccordionToggle();
});