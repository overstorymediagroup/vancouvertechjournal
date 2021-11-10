jQuery(document).ready(function ($) {
	
	/* Open Author info on button click */
	$('.toggle-info').click(function(){
        var author_id = $(this).data("id");
		$('.backdrop-'+author_id).animate({'opacity':'.50'}, 300, 'linear').css('display', 'block');
		$('.box-'+author_id).fadeIn();
	});
	$('.bio-btn').click(function(){
        var author_id = $(this).data("id");
		$('.backdrop-'+author_id).animate({'opacity':'.50'}, 300, 'linear').css('display', 'block');
		$('.box-'+author_id).fadeIn();
	});

	/* Click to close lightbox */
	$('.close, .backdrop').click(function(){
		$('.backdrop').animate({'opacity':'0'}, 300, 'linear', function(){
			$('.backdrop').css('display', 'none');
		});
		$('.box').fadeOut();
	});
	
	
   $('#copy_link').click(function (e) {
      e.preventDefault();
      var copyText = $(this).attr('href');

      document.addEventListener('copy', function (e) {
         e.clipboardData.setData('text/plain', copyText);
         e.preventDefault();
      }, true);

      document.execCommand('copy');
      console.log('copied text : ', copyText);
      alert('Copied to clipboard: ' + copyText);
   });

   $('input[name=essential-radio]').on('click', function () {
      $('input[name=essential-radio]').prop('checked', false);

      $(this).prop('checked', true);
      $(this).closest("form").submit();
   });

   $('body').find('input#billing_birth_year').attr('type', 'date');

   $('#billing_birth_day_field, #billing_birth_month_field, #billing_birth_year_field').wrapAll('<div class="birth-wrap"></div>');
   $('.single-product .summary, .single-product .woocommerce-checkout').wrapAll('<div class="checkout-form-wrap"></div>');
   $('body').find('.birth-wrap').prepend('<label for="billing_email" class="">Birthday&nbsp;<abbr class="required" title="required">*</abbr></label>');

   $(".single-product .single_add_to_cart_button").text("Update Plan");
   $(".single-product #team_name").val("Your team name");
   $(".single-product button#place_order").text("SUBSCRIBE");
   $(".post-631 .woocommerce-billing-fields h3").text("Step 1 of 2. Account Information");
$(".post-628 .woocommerce-billing-fields h3").text("Step 1 of 2. Account Information");
});