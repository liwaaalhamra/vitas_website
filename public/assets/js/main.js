$.ajaxSetup({
	headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	}
});
//Disable Image Draging
window.ondragstart = function () {return false;}
//Disable Right Click On Images
$("img").bind('contextmenu', function (e) {return false;});

$(window).on("load", function () {
	$(".loadingScreen").fadeOut();

	AOS.refresh();
});

$(window).scroll(function () {
	var scroll = $(window).scrollTop();
	if (scroll > 0) {
		$(".top-bar").addClass("onScroll");
	} else {
		$(".top-bar").removeClass("onScroll");
	}

	$('.bars').each(function () {
		if (scroll > $(".bars").offset().top - 300) {
			$(".bar").each(function () {
				var percent = $(this).data("percentage");
				$(this).find("span").css("width", percent + "%");
			});
		}
	});
});

$(document).ready(function () {

	// Library Animation on Scroll
	AOS.init({
		once: true
	});

	// Language Switcher
	$(".language").change(function(){
		window.location = $(this).val();
	});

	// Custom Slick
	$('.custom-slick').each(function () {
		$(this).slick({
			arrows: false,
			dots: true,
			autoplay: true,
			autoplaySpeed: 5000,
			pauseOnHover: false
		})
	});

	// Success Story Slick
	$('.success-story-slick').each(function () {
		$(this).slick({
			arrows: false,
			dots: true,
			autoplay: false,
			autoplaySpeed: 5000,
			pauseOnHover: false,
			infinite: false,
			slidesToShow: 2.5,
			slidesToScroll: 1,
			responsive: [{
				breakpoint: 991,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			},
			{
				breakpoint: 767,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			}, {
				breakpoint: 576,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}
			]
		})
	});

	// Our History Slick
	$('.our-history-slick').each(function () {
		$(this).slick({
			arrows: true,
			dots: false,
			autoplay: false,
			infinite: false,
			slidesToShow: 4,
			slidesToScroll: 1,
			vertical: true,
			verticalSwiping: false,
			swipe: false
		})
	});

	// FAQs list collapse
	$(".faqs-list li img").click(function () {
		$(this).toggleClass("active");
		$(this).closest("li").find(".content").slideToggle();
	});

	// Custom Select Box
	$('.custom-select-wrapper input[readonly], .custom-select-wrapper svg').on('click', function () {
		$(this).closest('.custom-select-wrapper').find('ul').toggleClass('d-block');
	});
	$('.custom-select-wrapper ul li').on('click', function () {
		var $this = $(this);
		$this.closest('.custom-select-wrapper').find('input[type="hidden"]').val($this.data('value'));
		$this.closest('.custom-select-wrapper').find('input[readonly]').val($this.text());
		$this.closest('.custom-select-wrapper').find('ul').removeClass('d-block');
	});
	$(document).bind('click', function (e) {
		if ($(e.target).closest('.custom-select-wrapper').length) return;
		$('.custom-select-wrapper ul').removeClass('d-block');
	});
	$('.custom-select-wrapper').each(function(){
		$(this).find('ul li').each(function(){
			var li = $(this).attr('selected');
			if (typeof li !== typeof undefined && li !== false) {
				$(this).trigger('click');
			}
		});
	});

	$('.countries-list ul li').on('click', function () {
		var $this = $(this);
		if($this.attr('data-value') == "Iraq" || $this.attr('data-value') == "العراق"){
			$(".governorates-list").removeClass('d-none');

			$(".mobile_number_pattern").val("+964");
			$(".mobile_number_pattern").attr("maxlength", "14");
		} else {
			$(".governorates-list").addClass('d-none');

			$(".mobile_number_pattern").val("");
			$(".mobile_number_pattern").attr("maxlength", "");
		}
	});
	$('.countries-list').each(function(){
		$(this).find('ul li').each(function(){
			var li = $(this).attr('selected');
			if (typeof li !== typeof undefined && li !== false) {
				$(this).trigger('click');
			}
		});
	});

	// Custome Type File
	$("input[type='file']").change(function () {
		var filePath = $(this).val().replace("C:\\fakepath\\", "");
		$(this).next().text(filePath);
	});

});

/*
|--------------------------------------------------------------------------
| AJAX CONTACT FORMS
|--------------------------------------------------------------------------
*/

$(".formSerialize").submit(function(e){
	e.preventDefault();
	var form = $(this);
	var htmlResponse = '';
	var btn_text = form.find("button[type='submit'] b").attr('data-text');
	var btn_loading_text = form.find("button[type='submit'] b").attr('data-loading-text');

	$.ajax({
		type  : "post",
		url   : form.attr("action"),
		data  : form.serialize(),
		beforeSend: function(data) {
			form.find("button[type='submit'] b").text(btn_loading_text);
			form.find("button[type='submit']").prop("disabled", true);
		},
		error: function(data){
			if (data.status == 422) {
				$.each(data.responseJSON.errors, function(key, val){
					htmlResponse += '<li>' + val[0] + '</li>';
				});
			} else {
				htmlResponse = '<li>Unexpected error</li>';
			}
			$(".alert-danger").html(htmlResponse);
			$(".alert-danger").slideDown();
		},
		success: function(data){
			if(data.code == 1){
				$(".alert-danger").hide();
				form[0].reset();
				$(".alert-success").text(data.message);
				$(".alert-success").slideDown();
				setTimeout(function(){
					$(".alert-success").slideUp();
				}, 3000);
			}
		},
		complete: function(){
			form.find("button[type='submit'] b").text(btn_text);
			form.find("button[type='submit']").prop("disabled", false);
		}
	});
});

$(".formData").submit(function(e){
	e.preventDefault();
	var form = $(this);
	var htmlResponse = '';
	var btn_text = form.find("button[type='submit'] b").attr('data-text');
	var btn_loading_text = form.find("button[type='submit'] b").attr('data-loading-text');

	$.ajax({
		type  : "post",
		url   : form.attr("action"),
		data  : new FormData(form[0]),
		processData: !1,
		contentType: !1,
		beforeSend: function(data) {
			form.find("button[type='submit'] b").text(btn_loading_text);
			form.find("button[type='submit']").prop("disabled", true);
		},
		error: function(data){
			if (data.status == 422) {
				$.each(data.responseJSON.errors, function(key, val){
					htmlResponse += '<li>' + val[0] + '</li>';
				});
			} else {
				htmlResponse = '<li>Unexpected error</li>';
			}
			$(".alert-danger").html(htmlResponse);
			$(".alert-danger").slideDown();
		},
		success: function(data){
			if(data.code == 1){
				$(".alert-danger").hide();
				form[0].reset();
				$(".alert-success").text(data.message);
				$(".alert-success").slideDown();
				setTimeout(function(){
					$(".alert-success").slideUp();
				}, 3000);
			}
		},
		complete: function(){
			form.find("button[type='submit'] b").text(btn_text);
			form.find("button[type='submit']").prop("disabled", false);
		}
	});
});