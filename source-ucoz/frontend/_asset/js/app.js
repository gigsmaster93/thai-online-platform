$(document).ready(function(){

	var sliderTouch = 120;

	/* ==========================================================================
	 Hamburger Menu
	 ========================================================================== */

	$('.hamburger').click(function(){
		var burger = $(this),
			classAct = 'is-active',
			classOpn = 'menu-opened';

		if ( !burger.hasClass(classAct) ) {
			burger.addClass(classAct);
			$('body').addClass(classOpn);
		} else {
			burger.removeClass(classAct);
			$('body').removeClass(classOpn)
		}
	});


	/* ==========================================================================
	 Fancybox
	 ========================================================================== */

	$("[data-fancybox]").fancybox({
		buttons : ['zoom','close'],
		iframe : {
			css : {
				"max-width" : "80%",
				"max-height" : "80%"
			}
		},
		youtube : {
			controls : 1,
			showinfo : 0
		},
		vimeo : {
			color : 'ffc000'
		}
	});


	/* ==========================================================================
	 Placeholder Phone
	 ========================================================================== */

	$('.js-phone').mask('+7 000-000-00-00');


	/* ==========================================================================
	 Buttons
	 ========================================================================== */

	$('.js-to').click(function(e){
		e.preventDefault();

		$('body,html').animate({
			scrollTop: $($(this).attr('href')).offset().top
		}, 1000);

		$('.hamburger').removeClass('is-active');
		$('body').removeClass('menu-opened')

	});


	/* ==========================================================================
	 Modal
	 ========================================================================== */

	$('.js-modal').each(function(){
		var link = $(this),
			target = $(link.data('target')),
			btnClose = target.find('.js-modal-close'),
			bodyClass = 'modal-opened',
			modalClass = 'opened';

		link.click(function(e){
			e.preventDefault();

			$('body').addClass(bodyClass);
			target.addClass(modalClass);
		});

		btnClose.click(function(e){
			e.preventDefault();

			$('body').removeClass(bodyClass);
			target.removeClass(modalClass);
		});
	});


	/* ==========================================================================
	 Dropdown
	 ========================================================================== */

	$('.js-dd-wrapper').each(function(){
		var parent = $(this),
			popup = parent.find('.js-dd-popup'),
			btnOpen = parent.find('.js-dd-btn'),
			btnClose = parent.find('.js-dd-close'),
			classOpen = 'opened',
			link = popup.find('a');

		btnOpen.click(function(e){
			e.preventDefault();

			if (popup.is(':hidden')) {
				popup.show();
				parent.addClass(classOpen);
			} else {
				popup.hide();
				parent.removeClass(classOpen);
			}
		});

		btnClose.click(function(){
			popup.hide();
			parent.removeClass(classOpen);
		});

		$('html').click(function(event) {
			if (
					!$(event.target).closest(popup).length
					&&
					!$(event.target).is(popup)
					&&
					!$(event.target).closest(btnOpen).length
					&&
					!$(event.target).is(btnOpen)
			) {
				popup.hide();
				parent.removeClass(classOpen);
			}
		});
	});


	/* ==========================================================================
	 Section Slider
	 ========================================================================== */

	$('.sct-slider__slide').each(function(){
		var item = $(this),
			path = item.find('img').attr('src'),
			sliderHeight = $(window).height() - $('.sct-main').outerHeight();

		if ( sliderHeight >= $(window).height()/5 && $(window).width() > 997 ) {
			item.height(sliderHeight);
		}

		item.find('a').css('background-image','url(' + path + ')');
	});

	var mainSlider = $('.sct-slider__in'),
		arrLeft = '<button type="button" class="arr arr-left"><svg viewBox="0 0 43 43"><circle class="cls-1" cx="21.5" cy="21.5" r="20"/><path id="Rectangle_7_copy" data-name="Rectangle 7 copy" class="cls-2" d="M880.538,497.993l-4.531-7.493,4.531-7.493" transform="translate(-858.5 -468.5)"/></svg></button>',
		arrRight = '<button type="button" class="arr arr-right"><svg viewBox="0 0 43 43"><path class="cls-1" d="M939.3,483.007l4.694,7.493-4.694,7.493" transform="translate(-918.5 -468.5)"/><circle id="Ellipse_1_copy" data-name="Ellipse 1 copy" class="cls-2" cx="21.5" cy="21.5" r="20"/></svg></button>';

	mainSlider.slick({
		arrows: false,
		infinite: true,
		slidesToShow: 3,
		slidesToScroll: 1,
		touchThreshold: sliderTouch,
		responsive: [
			{
				breakpoint: 997,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1,
					infinite: true,
					arrows: true,
					prevArrow: arrLeft,
					nextArrow: arrRight,
					touchThreshold: sliderTouch
				}
			},
			{
				breakpoint: 800,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1,
					infinite: true,
					arrows: true,
					prevArrow: arrLeft,
					nextArrow: arrRight,
					touchThreshold: sliderTouch
				}
			},
			{
				breakpoint: 540,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					infinite: true,
					arrows: true,
					prevArrow: arrLeft,
					nextArrow: arrRight,
					touchThreshold: sliderTouch
				}
			}
		]
	});

	mainSlider.on('init', function(slick){
		$('.sct-slider__slide').each(function() {
			var item = $(this),
					path = item.find('img').attr('src');

			item.css('background-image', 'url(' + path + ')');
		});
	});


	// Arrows
	$('.js-slider-prev').click(function(){
		mainSlider.slick('slickPrev');
	});
	$('.js-slider-next').click(function(){
		mainSlider.slick('slickNext');
	});


	// Button Open Slider
	$('.js-open-slider').click(function(){
		$('a[data-fancybox="gallery"]').first().trigger('click');
	});


	// Count of Slides
	var totalMainSlider = $('.sct-slider .slick-slide:not(.slick-cloned)').length;

	$('.js-total-slides').text(totalMainSlider);


	/* ==========================================================================
	 Fixed Header
	 ========================================================================== */

	var fixHead = $('.fix-header');

	$(window).scroll(function(){
		if ( $(this).scrollTop() > ($('.sct-main').outerHeight() + $('.sct-slider').outerHeight()) ) {
			fixHead.addClass('is-show');
		} else {
			fixHead.removeClass('is-show');
		}
	});


	/* ==========================================================================
	 Benefits Slider
	 ========================================================================== */

	var sliderArrNext = '<button type="button" class="slick-arrow thin slick-next"><svg viewBox="0 0 37.969 103.88"><path class="cls-1" d="M330,2320.43L297.574,2270,330,2219.57" transform="translate(-296.062 -2218.06)"/></svg></button>';

	$('.benefits-slider__in').slick({
		infinite: true,
		speed: 300,
		slidesToShow: 1,
		variableWidth: true,
		nextArrow: sliderArrNext,
		touchThreshold: sliderTouch
	});


	/* ==========================================================================
	 Section Layouts
	 ========================================================================== */

	$('.layout-card__photo').each(function(){
		var item = $(this),
			path = item.find('img').attr('src');

		item.css('background-image','url(' + path + ')');
	});

	$('.layout-slider__in').slick({
		infinite: true,
		speed: 500,
		slidesToShow: 1,
		variableWidth: true,
		nextArrow: sliderArrNext,
		touchThreshold: sliderTouch
	});


	/* ==========================================================================
	 Section Calculator
	 ========================================================================== */

	// Range Sliders
	$(".js-range-slider").each(function(){
		var item = $(this),
				target = item.data('target');

		item.ionRangeSlider({
			onUpdate: function(data){

				$(target).text(data.from_pretty)
			},
			onStart: function(data){

				$(target).text(data.from_pretty)
			},
			onChange: function(data){

				$(target).text(data.from_pretty)
			}
		});
	});

	// Tabs
	$('.calc-tabs').each(function(){
		var item = $(this),
			navLink = item.find('.calc-tabs__nav span');

		navLink.click(function(){
			if ( !$(this).hasClass('active') ) {
				navLink.not(this).removeClass('active');
				$(this).addClass('active');

				item.find('.calc-tabs__content').removeClass('active');
				item.find('.calc-tabs__content').eq($(this).index()).addClass('active');
			}
		});
	});




	/*  ========================================================================== */

});