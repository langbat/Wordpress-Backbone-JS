<?php
/**
 * Template Name: Unternehmensprofil
 */
global $current_user;

$company_label = get_field('company_certificate_pa', 'user_' . $current_user->ID);

$dash = get_page(6);

//get acf plugin for frontend-output
acf_form_head();


get_header();
?>
<div class="row-fluid" id="body_container">
    <div class="content-block" id="wrapper">
        <div class="heading"> 
            <h1 class="title"><?php _e("ACCOUNT", ET_DOMAIN); ?></h1>	 
        </div>

        <div id="page_zertifikat">
            <div class="account-title">
                <div class="main-center clearfix">
                    <ul class="account-menu">
                        <?php do_action('je_before_company_info_tab') ?>
                        <li><a href="<?php echo et_get_page_link('dashboard'); ?>"><?php _e('YOUR JOBS', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('profile'); ?>" ><?php _e('COMPANY PROFILE', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('password'); ?>"><?php _e('PASSWORD', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('verbandverwaltung'); ?>" ><?php _e('Verbandverwaltung', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('zertifikat'); ?>" class="active"><?php _e('praktischArzt-Zertifikat', ET_DOMAIN); ?></a></li>
                        <?php do_action('je_after_company_info_tab') ?>
                    </ul>        
                </div>
            </div>

            <div class="main-column">
                <div class="form-account prime-form " id="zertifikat_step1"> 
                    <div class="row-fluid">
                        <div class="span6">
                            <div class="form-item">
                                <div class="row-fluid"> 
                                    <b>
                                        <?php _e("Machen Sie auf herausragende Arbeitsbedingungen in Ihrer Klinik aufmerksam", ET_DOMAIN); ?>
                                    </b> 
                                </div>
                                <div class="row-fluid"> 
                                    <div class="certificate">
                                        <ul>
                                            <li><?php _e("Das Zertifikat signalisiert überdurchschnittlich gute Konditionen auf den ersten Blick", ET_DOMAIN); ?></li>
                                            <li><?php _e("Heben Sie sich von anderen Stellenanzeigen ab", ET_DOMAIN); ?></li>
                                        </ul>
                                    </div> 
                                </div>
                            </div>
                        </div>
                        <div class="span6 step1_right">

                            <div class="span6">
                                <img src="<?php echo get_template_directory_uri() ?>/img/praktischArzt-Zertifikat.png" alt="praktischArzt Zertifikat"/>
                            </div>
                            <?php
                            //echo $company_label;
                            if (!$company_label) {
                                ?>
                                <div class="span6">
                                    <h4><?php _e("Das Zertifikat für 33 € im Monat!", ET_DOMAIN); ?></h4>
                                    <p>
                                        <?php _e("Die Bedingungen und Informationen finden Sie hier:", ET_DOMAIN); ?> 
                                    </p>
                                    <div class="buy_zertifikat">
                                        <div class="newsletter-btn" id="buy-zertifikat"><?php _e("Zur Buchung", ET_DOMAIN); ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div> 
                </div> 
                <div id="zertifikat_step2">
                    <div class="row-fluid">
                        <div class="span9">
                            <h4><?php _e("Wofür steht das praktischArzt-Zertifikat?", ET_DOMAIN); ?></h4>
                            <p>
                                <?php _e("Das praktischArzt Zertifikat wird in jeder Ihrer veröffentlichten Stellen angebracht und signalisiert, 
dass Sie attraktive Arbeitsbedingungen bieten. Zusätzlich wird direkt in den Suchergebnissen das 
Zertifikat an Ihre Stelle angebracht. Somit werden Studenten direkt bei ihrer Recherche auf Sie 
aufmerksam gemacht.
", ET_DOMAIN); ?> 
                            </p>
                        </div>
                        <div class="span3">
                            <img src="<?php echo get_template_directory_uri() ?>/img/praktischArzt-Zertifikat.png" alt=""/>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <h4><?php _e("Mit dem praktischArzt-Zertifikat sorgen Sie dafür, dass Ihre veröffentlichten Stellen schneller besetzt werden!", ET_DOMAIN); ?></h4>
                        <p>
                            <?php _e("Anbei sind die Konditionen aufgeführt, die Sie erfüllen muss, um für das praktischArzt-Zertifikat in Frage zu kommen.", ET_DOMAIN); ?> 
                        </p>

                        <h4><?php _e("Die Pflicht-Bedingungen für das praktischArzt-Zertifikat: ", ET_DOMAIN); ?><span class="icon_zertifikat"><img src="<?php echo get_template_directory_uri() ?>/img/Haken_lila.png" alt=""/></span></h4>
                        <form method="post" action="" id="zertifikat_form"> 
                            <input type="submit" id="zertifikat_submit" style="display: none"/>
                            <input type="hidden" name="id" value="<?php echo $current_user->ID ?>">
                            <div class="row-fluid check_zertifikat">
                                <div class="span4">
                                    <label for="famulaturen"><?php _e("Für Famulaturen", ET_DOMAIN); ?></label><input type="checkbox" required id="famulaturen" name="famulaturen" value=""/>*
                                    <ul class="dec_zertifikat">
                                        <li><?php _e("Möglichkeit der Teilnahme an Fortbildungen", ET_DOMAIN); ?></li>
                                        <li><?php _e("Kostenloses Mittagessen", ET_DOMAIN); ?></li>
                                        <li><?php _e("Betreuung durch Ober- und Chefarzt", ET_DOMAIN); ?></li>
                                        <li><?php _e("Kostenlose Unterkunft (z.B. im Schwesternwohnheim)", ET_DOMAIN); ?></li>
                                        <li><?php _e("Kostenloses Fachlehrbuch/Lehrmaterial", ET_DOMAIN); ?></li>
                                    </ul>
                                </div>
                                <div class="span4">
                                    <label for="praktisches"><?php _e("Für das PJ", ET_DOMAIN); ?></label><input type="checkbox" required id="praktisches" name="praktisches" value=""/>*
                                    <ul class="dec_zertifikat">
                                        <li><?php _e("Aufwandsentschädigung", ET_DOMAIN); ?></li>
                                        <li><?php _e("Möglichkeit der Teilnahme an Fortbildungen", ET_DOMAIN); ?></li>
                                        <li><?php _e("Studientag", ET_DOMAIN); ?></li>
                                        <li><?php _e("Möglichkeit der Betreuung eigener Patienten", ET_DOMAIN); ?></li>
                                        <li><?php _e("Kostenloses Mittagessen", ET_DOMAIN); ?></li>
                                    </ul>
                                </div>
                                <div class="span4">
                                    <label for="assisten"><?php _e("Für Assistenzarztstellen", ET_DOMAIN); ?></label><input type="checkbox" required id="assisten" name="assisten" value=""/>*
                                    <ul class="dec_zertifikat">
                                        <li><?php _e("Tarifliche oder übertarifliche Vergütung", ET_DOMAIN); ?></li>
                                        <li><?php _e("Erfassung und Abgeltung von Überstunden", ET_DOMAIN); ?></li>
                                        <li><?php _e("Weiterbildungscurriculum", ET_DOMAIN); ?></li>
                                        <li><?php _e("Regelmäßige Feedbackgespräche", ET_DOMAIN); ?></li>
                                        <li><?php _e("Finanzielle Unterstützung/ Freistellung für Fortbildungen", ET_DOMAIN); ?></li>
                                    </ul>
                                </div>
                            </div>
                            <h4><?php _e("Die optionalen Bedingungen für das praktischArzt-Zertifikat: ", ET_DOMAIN); ?><span class="icon_zertifikat"><img src="<?php echo get_template_directory_uri() ?>/img/Haken_blau.png" alt=""/></span></h4>
                            <div class="row-fluid check_zertifikat">
                                <div class="span4">
                                    <label for="famulaturen2"><?php _e("Für Famulaturen", ET_DOMAIN); ?></label><input type="checkbox" id="famulaturen2" name="" value=""/>
                                    <ul class="dec_zertifikat"> 
                                        <li><?php _e("Kostenlose Unterkunft (z.B. im Schwesternwohnheim)", ET_DOMAIN); ?></li>
                                        <li><?php _e("Kostenloses Fachlehrbuch/Lehrmaterial", ET_DOMAIN); ?></li>
                                    </ul>
                                </div>
                                <div class="span4">
                                    <label for="praktisches2"><?php _e("Für das PJ", ET_DOMAIN); ?></label><input type="checkbox" id="praktisches2" name="" value=""/>
                                    <ul class="dec_zertifikat">
                                        <li><?php _e("PJ in Teilzeit möglich", ET_DOMAIN); ?></li>
                                        <li><?php _e("Kostenlose Unterkunft", ET_DOMAIN); ?></li>
                                        <li><?php _e("Hilfe bei der Suche einer Kinderbetreuung", ET_DOMAIN); ?></li> 
                                    </ul>
                                </div>
                                <div class="span4">
                                    <label for="assisten2"><?php _e("Für Assistenzarztstellen", ET_DOMAIN); ?></label><input type="checkbox" id="assisten2" name="" value=""/>
                                    <ul class="dec_zertifikat">
                                        <li><?php _e("Teilzeitmodelle möglich", ET_DOMAIN); ?></li> 
                                        <li><?php _e("Hilfe bei der Suche einer Kinderbetreuung (z.B. klinikeigene KiTa)", ET_DOMAIN); ?></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row-fluid">
                                <p>
                                    <?php _e("* Mit der Buchung des praktischArzt-Zertifikates bestätige ich, dass die Angaben zur Erfüllung der 
Pflicht-Bedingungen wahrheitsgemäß getätigt wurden.", ET_DOMAIN); ?> 
                                </p>
                                <p>
                                    <?php _e("Nach Erhalt der Zahlung wird das Zertifikat mit Ihrem Profil und Ihren derzeitigen und zukünftigen Stellen 
während der gesamten Laufzeit des Zertifikates verknüpft. Nach Ablauf der Laufzeit wird das Zertifikat 
automatisch für Sie verlängert. Das Zertifikat können Sie unkompliziert jederzeit mit einer Kündigungsfrist
von einem Monat vor Ablauf der Laufzeit (Die Laufzeit beträgt jeweils 12 Wochen) kündigen.", ET_DOMAIN); ?> 
                                </p>
                            </div>
                            <div class="row-fluid">
                                <div class="label">
                                    <ul>
                                        <li class="checkout_package"><h4>Gewähltes Paket:</h4> 
                                            <span class="entry">  
                                                praktischArzt-Zertifikat
                                            </span></li> 
                                        <li class="checkout_duration">Laufzeit: 
                                            <span class="entry">12</span> Wochen
                                        </li>

                                        <li class="checkout_total"><h3>Gesamtkosten des Pakets:</h3> 
                                            <div>
                                                <label class="price_entry"> Paketpreis  </label>
                                                <span class="price_entry"> 99.00 €</span>
                                            </div>
                                            <div>	
                                                <label class="vat_entry"> zzgl. 19% MwSt.</label>
                                                <span class="vat_entry"> 18.81 €</span>
                                            </div>
                                            <div>
                                                <label class="total_entry"> Zu zahlender Betrag</label>
                                                <span class="total_entry"> 117.81 €</span>
                                            </div>

                                        </li>
                                    </ul>
                                </div>
                                <ul>
                                    <li>
                                        <input type="checkbox" id="agb_check" name="agb_check" required class="">  <label for="agb_check">Ich habe die <a href="<?php bloginfo('url'); ?>/agb/" target="about:blank">AGB gelesen</a></label> 
                                    </li>
                                    <li class="clearfix">
                                        <div class="f-left">
                                            <div class="title">Lastschrift</div> 
                                            <div class="desc"></div> 
                                        </div> 
                                        <div class="f-right">
                                            <div class="btn-select ">
                                                <div class="btn bg-btn-hyperlink border-radius select_payment_debit" data-price="99" data-gateway="debit" ><span>Jetzt kaufen</span></div>
                                            </div> 

                                            <div id="form_payment_debit">

                                                <div class="control-group">
                                                    <label class="control-label" for="inputPayer">Kontoinhaber</label>
                                                    <input type="text" id="inputPayer" placeholder="Kontoinhaber"> 
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label" for="inputAmount">Rechnungsbetrag</label> 
                                                    <input type="text" id="inputAmount" value="117.81" placeholder="Rechnungsbetrag" readonly="readonly" class="icon-euro"> 
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label" for="accountNumber">Kontonummer</label> 
                                                    <input type="text" id="accountNumber" name="accountNumber" placeholder="Kontonummer"> 
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label" for="bankNumber">Bankleitzahl</label> 
                                                    <input type="text" id="bankNumber" name="bankNumber" placeholder="Bankleitzahl"> 
                                                </div> 
                                                <div class="clearfix"></div>
                                                <div class="btn-select"> 
                                                    <div class="btn bg-btn-hyperlink border-radius payment_debit" data-price="99" data-gateway="debit" ><span>Jetzt kaufen</span></div>
                                                </div> 
                                            </div>
                                        </div> 
                                    </li>
                                    <?php
                                    $je_default_payment = array('google_checkout', 'paypal', 'cash', '2checkout');
                                    $payment_gateways = et_get_enable_gateways();
                                    do_action('before_je_payment_button', $payment_gateways);
                                    foreach ($payment_gateways as $key => $payment) {
                                        if (!isset($payment['active']) || $payment['active'] == -1 || !in_array($key, $je_default_payment))
                                            continue;
                                        ?>
                                        <li class="clearfix">
                                            <div class="f-left">
                                                <div class="title"><?php echo $payment['label'] ?></div>
                                                <?php if (isset($payment['description'])) { ?>
                                                    <div class="desc"><?php echo $payment['description'] ?></div>
                                                <?php } ?>
                                            </div>

                                            <div class="btn-select f-right">
                                                <button class="bg-btn-hyperlink border-radius select_payment" data-price="99" data-gateway="<?php echo $key ?>" > Jetzt kaufen</button>
                                            </div>

                                        </li>
                                        <?php
                                    }
                                    do_action('after_je_payment_button', $payment_gateways);
                                    ?>
                                </ul>
                            </div>
                        </form>
                    </div> 
                </div>
            </div>      <!-- end .main-column -->  
            <?php
            global $current_user, $wp_query;
            $cur_user = et_create_companies_response($current_user);
            ?>
            <script type="application/json" id="zertifikat_data">
                <?php echo json_encode($cur_user); ?>
            </script>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo get_template_directory_uri() ?>/js/prettyCheckable.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(document).ready(function($) {
            $('#page_zertifikat .check_zertifikat input[type=checkbox]').prettyCheckable({
                labelPosition: '', color: 'blue'
            });
            JobEngine.Views.Zertifikat = Backbone.View.extend({
                el: $('#page_zertifikat'),
                events: {
                    'click #buy-zertifikat': 'ShowZertifikat',
                    'click #zertifikat_form .select_payment': 'selectPayment',
                    'click div.select_payment_debit': 'showFormDebit',
                    'click div.payment_debit': 'PaymentDebit'
                },
                initialize: function() {
                    var that = this;
                    console.log('Backbone View Zertifikat');
                    this.$form = this.$('form#zertifikat_form');
                    this.model = new JobEngine.Models.Company(JSON.parse(this.$('#zertifikat_data').html()));
                    console.log(this.model.toJSON());

                },
                ShowZertifikat: function(event) {
                    event.preventDefault();
                    $("#zertifikat_step1").hide();
                    $("#zertifikat_step2").show();
                },
                selectPayment: function(event) {
                    event.preventDefault();
                    var that = this;
                    that.zertifikat_form_validator = that.$('form#zertifikat_form').validate({
                        ignore: "", rules: {
                            famulaturen: "required",
                            praktisches: "required",
                            assisten: "required",
                            agb_check: "required"
                        },
                        errorPlacement: function(label, element) {
                            label.insertAfter(element);
                        }
                    });

                    var paymentType = this.$(event.currentTarget).attr('data-gateway');
                    var price = this.$(event.currentTarget).attr('data-price');
                    console.log(paymentType);
                    console.log(price);
                    var loading = new JobEngine.Views.LoadingButton({el: $(event.currentTarget)});
                    var params = {type: 'POST',
                        dataType: 'json',
                        url: et_globals.ajaxURL,
                        contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
                        data: {
                            action: 'et_payment_zertifikat',
                            method: 'payment_zertifikat',
                            authorID: this.model.get('id'),
                            paymentType: paymentType,
                            price: price,
                            debit: (paymentType == 'debit')?this.getDebitData():''
                        },
                        beforeSend: function() {
                            loading.loading();
                        },
                        success: function(response) {
                            loading.finish();

                            if (response.success) {
                                if (response.data.ACK) {

                                    $('#zertifikat_form').attr('action', response.data.url);
                                    if (typeof response.data.extend !== "undefined") {
                                        $('#zertifikat_form .payment_info').html('').append(response.data.extend.extend_fields);
                                    }

                                    $('#zertifikat_submit').click();
                                }
                            } else {
//                                pubsub.trigger('je:notification', {
//                                    msg: response.errors[0],
//                                    notice_type: 'error'
//                                });
                            }

                        }
                    };

                    // check agb_checkbox before submiting
                    if (this.$('form#zertifikat_form').valid()) {
                        // if valid submit form
                        jQuery.ajax(params);

                    } else { // trigger event to show error message
//                        pubsub.trigger('je:notification', {
//                            msg: et_post_job.error_msg,
//                            notice_type: 'error'
//                        });
                    }
                },
                showFormDebit: function(event) {
                    $(event.currentTarget).hide();
                    //$("#form_payment_debit #inputPayer").val(this.model.get('display_name'));
                    $("#form_payment_debit").slideDown();
                },
                
                getDebitData: function(){
                    var price = $("#inputAmount").val();
                    var accountNumber = $("#accountNumber").val();
                    var bankNumber = $("#bankNumber").val();

                    return {
                        authorID: this.model.get('id'),
                        payer: this.model.get('display_name'),
                        price: price,
                        accountNumber: accountNumber,
                        bankNumber: bankNumber
                    };
                },
                
                PaymentDebit: function(event) {
                    this.checkout_form_validator = this.$('form#zertifikat_form').validate({
                        ignore: "", rules: {
                            bankNumber: "required",
                            accountNumber: "required"
                        },
                        errorPlacement: function(label, element) {
                            label.insertAfter(element);
                        }
                    });
                    
                    return this.selectPayment(event);
                }

            });

            JobEngine.Zertifikat = new JobEngine.Views.Zertifikat();

        });
    })(jQuery);
</script>
<?php get_footer(); ?>