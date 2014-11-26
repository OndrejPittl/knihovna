$(document).ready(function() {
    	
	tinyMCE_init();



	// //javascriptem nastavuju velikost "okna", ktery obsahuje veskery obsah stranek,
	// //u kontaktu trva dele, nez se nacte obsah, podle ktereho se nastavi velikost okna,
	// //proto takovehle "manevry"

	//if(parseInt($('div#content').css("height")) <= 0) {
		//console.log($('div#content>div').css("height"));
		$timeout = 200;

		if(window.location.pathname == '/contact/'){
			$timeout = 750;
		}

		setTimeout(function(){
			resizeContentDiv();
		}, $timeout)
	//}


	if(window.location.pathname == '/browsing/'){
		//prohlizeni

        if (($visited = $.cookie("visited")) == null) {

        	$('div.bubble').css({"display":"inline-block"});
        	positionBubble();
        }

        $.cookie('visited', 'yes', { expires: 1, path: '/' });
	}

	$resizing = false;
	$(window).resize(function(){
		
		if(!$resizing){
			setTimeout(function(){
				resizeContentDiv();
				$resizing = false;
			}, 100);

			$resizing = true;
		}

		if(window.location.pathname == '/browsing/'){
			positionBubble();
		}
	})





	/*
		--------------------------------
		------------ EVENTS ------------
	*/
	
	$('nav ul li a, div#footer a').click(function(event){		
			$link = $(this).parent().attr("data-link");

			$(this).parent().siblings().removeClass("active");
			$(this).parent().addClass("active");

			$href = '';
			if(($href=$(this).attr("href")) != null){

				$.when($('form#search').animate({'height':'0px'}, 500)).then($('form#search').animate({'display':'none'}, 1));

				$('div#content').animate({"height":"0px", "padding":"0"}, 500, function(){
						window.location.href = $href;
				});


			} else {
				//jsme na prihlasovaci strance

				if($('form#signup').length > 0 || $('form#login').length > 0) {
					$.get("view/twig/templates/forms/" + $link + "_form.html", function(response){
						$('div#content>div').css("opacity", 0);
						$.when($('div#content>div').html(response)).then(function(){
							$('div#content>div').animate({"opacity":1}, 500);
							$paddingy = parseInt($('div#content').css("paddingTop")) + parseInt($('div#content').css("paddingTop"));
							$('div#content').animate({"height": parseInt($('div#content>div').css("height")) + $paddingy}, 500);	
						})
					});	
				}		
			}
	});


	$('body').on('click', 'form#signup input[type=submit]', function (event){	
		validateSignupForm();

		if($('form#signup')[0].checkValidity()){
			if(!validateSignupForm()) {
				event.preventDefault();	
			}
		}
    });


    $('table.table-striped tr.tr_clickable').click(function(event){
    	if(event.target.tagName != 'A') {
    		$(this).next('tr').toggleClass('tr_active');	
    	}

    	$('div.bubble').css("display","none");


    	setTimeout(function(){
    		resizeContentDiv();
    	}, 400);
    })


    $('form#opravny_form').submit(function(event){
    	$passy_input = $('input[type=password]#input_heslo');
    	$passy = $passy_input.val();
    	
    	if($passy.trim().length > 0){
    		if($passy_input.next().val() != $passy) {

    			$passy_input.removeClass("correct_input").addClass("incorrect_input");
    			$passy_input.next().removeClass("correct_input").addClass("incorrect_input");

    			event.preventDefault();
    		}
    	} else {
    		$passy_input.removeClass("correct_input").addClass("incorrect_input");
    		$passy_input.next().removeClass("correct_input").addClass("incorrect_input");

    		event.preventDefault();
    	}

    });



    $('input[type=submit].warning, a.warning').click(function(event){
    	if(!confirm("Opravdu si přejete provést změny?")){
    		event.preventDefault();
    	}
    })


	$('a.show_form').click(function(){
		if(parseInt($('a.show_form+div').css("max-height")) > 0) {
			$val = $('a.show_form+div>form').height()+100;
			$('a.show_form+div').animate({"max-height":0});
			$('div#content').animate({"height": $('div#content').height() - $val}, function(){
				$('a.show_form').html("přidat záznam");
			});
		} else {
			$val = $('a.show_form+div>form').height()+100;
			$('a.show_form+div').animate({"max-height":$val});	
			$('div#content').animate({"height": $('div#content').height() + $val}, function(){
				$('a.show_form').html("skrýt");
			});
		}
		
	})


	$('form#kom').submit(function(event){
    	$editorContent = tinyMCE.get('komentar_obsah').getContent();
    	console.log($editorContent);

		if ($editorContent == '') {
		    event.preventDefault();
		}

		$('div#comments output').val("Komentář nesmí být prázdný.");
		resizeContentDiv();
    });


	/*
		------------ EVENTS ------------
		--------------------------------
	*/
	


});


function resizeContentDiv(){
	$paddingy = parseInt($('div#content').css("paddingTop"))*2;
	$('div#content').animate({"height": $('div#content>div').height() + $paddingy}, 400);
}

function positionBubble(){
	$pos = $('table.table-striped.table_vypis tr:nth-of-type(2)').position();

   	$x = 0; $y = 0;
    if(($x = $pos.left - $('div.bubble').width()) < 0){
    	$x = 5;
    }

    $y = $pos.top - $('div.bubble').height()/3;

    $('div.bubble').css({"left":$x, "top":$y});
}

function validateSignupForm(){
	$result = true;

	$msges = [];
	$msg_output = $('form#signup p#output');

	$('form#signup input').each(function(){
		$name = $(this).attr("name");

		if($name != "signup"){
			//kontrola existence retezce v inputu	
			if($(this).val().trim().length <= 0) {
				if($(this).hasClass("correct_input")) $(this).removeClass("correct_input");
				$(this).addClass("incorrect_input");
				
				if($(this).attr("name") != undefined) {
					$msges.push($(this)[0].name.replace("signup_data[", "").replace("]","") + ": Nesmí být prázdné.");
				}
				$result = false;
			} else {
				if($(this).hasClass("incorrect_input")) $(this).removeClass("incorrect_input");
				$(this).addClass("correct_input");
			}


			if($name != null) {
				//kontrola data, zda je mensi nez aktualni
				if($name != null && $name.indexOf("datum_narozeni") >= 0) {
					$val = new Date($(this).val());
					$now = new Date();

					if($val != "" && $now > $val) {
						if($(this).hasClass("incorrect_input")) $(this).removeClass("incorrect_input");
						$(this).addClass("correct_input");
					} else {
						if($(this).hasClass("correct_input")) $(this).removeClass("correct_input");
						$(this).addClass("incorrect_input");
						$msges.push($(this)[0].name.replace("signup_data[", "").replace("]","") + ": Musí být < dnešní datum.");
						$result = false;
					}
				}

				if($name.indexOf("email") >= 0) {
					if(!isValidEmailAddress($(this).val())) {
						if($(this).hasClass("correct_input")) $(this).removeClass("correct_input");
						$(this).addClass("incorrect_input");
						$msges.push($(this)[0].name.replace("signup_data[", "").replace("]","") + ": Nesprávný formát.");
						$result = false;
					} else {
						if($(this).hasClass("incorrect_input")) $(this).removeClass("incorrect_input");
						$(this).addClass("correct_input");
					}
				}


				if($name.indexOf("passwd") >= 0) {
					$passwd1 = $(this).val();

					$nextPasswdInput = $($(this).next()[0]);
					$passwd2 = $nextPasswdInput.val();


					if($passwd1 == $passwd2 && $passwd1 != "" && $paswsd2 != "") {
						if($(this).hasClass("incorrect_input")) $(this).removeClass("incorrect_input");
						$(this).addClass("correct_input");

					} else {
						if($(this).hasClass("correct_input")) $(this).removeClass("correct_input");
						$(this).addClass("incorrect_input");
						
						if($(this).attr("name") != undefined) {
							$msges.push($(this)[0].name.replace("signup_data[", "").replace("]","") + ": Hesla nesmí být prázdná a musí se shodovat.");
						}
						
						$result = false;
					}					
				}
			}
		}		
	});
	

	$message = '';

	for($i=0;$i<$msges.length;$i++) {
		$message += "•" + $msges[$i] + "<br>";
	}

	$msg_output.html($message);
	resizeContentDiv();

	return $result;
}

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function tinyMCE_init(){
	tinymce.init({
		mode : "specific_textareas",
        editor_selector : /(komentare|mceRichText)/,
		menu : {
	        insert : {title : 'Insert', items : 'link media | template hr'},
	        table  : {title : 'Table' , items : 'inserttable tableprops deletetable | cell row column'},
	        tools  : {title : 'Tools' , items : 'spellchecker code'}
	    }, 
	    height : 170
	});
}
	//na Safari HTML5 validace existuje, nevola se vsak pri submit();
    function checkHTML5ValidationIsSupported(){
    	return (typeof document.createElement( 'input' ).checkValidity == 'function');
    }